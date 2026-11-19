<?php

namespace App\Services;

class RouterService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('router.baseUrl', 'http://127.0.0.1:20128/v1'), '/');
        $this->apiKey  = env('router.apiKey', '9router-internal-key');
        $this->timeout = (int) env('router.timeout', 60);
    }

    /**
     * Map agent preferred model tier to 9Router models
     */
    public function resolveModel(string $tier = 'coding', ?string $overrideModel = null): string
    {
        if ($overrideModel) {
            return $overrideModel;
        }

        return match ($tier) {
            'reasoning'  => 'claude-3-7-sonnet',
            'coding'     => 'deepseek-chat',
            'multimodal' => 'claude-3-5-sonnet',
            'planning'   => 'claude-3-7-sonnet',
            'fast'       => 'gemini-2.5-flash',
            default      => 'deepseek-chat',
        };
    }

    /**
     * Check if 9Router Gateway is reachable
     */
    public function checkHealth(): array
    {
        $ch = curl_init("{$this->baseUrl}/models");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 3,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$this->apiKey}",
                "Content-Type: application/json",
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode >= 400 || !$response) {
            return [
                'online'    => false,
                'status'    => 'offline',
                'url'       => $this->baseUrl,
                'http_code' => $httpCode,
                'error'     => $error ?: "HTTP {$httpCode}",
                'models'    => [],
            ];
        }

        $decoded = json_decode($response, true);
        $models = [];
        if (isset($decoded['data']) && is_array($decoded['data'])) {
            foreach ($decoded['data'] as $m) {
                if (isset($m['id'])) {
                    $models[] = $m['id'];
                }
            }
        }

        return [
            'online'    => true,
            'status'    => 'active',
            'url'       => $this->baseUrl,
            'http_code' => $httpCode,
            'models'    => $models,
        ];
    }

    /**
     * Standard Non-Streaming Chat Completion via 9Router
     */
    public function chat(array $messages, ?string $model = null, array $options = []): array
    {
        $payload = array_merge([
            'model'       => $model ?: 'deepseek-chat',
            'messages'    => $messages,
            'temperature' => 0.7,
            'stream'      => false,
        ], $options);

        $ch = curl_init("{$this->baseUrl}/chat/completions");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$this->apiKey}",
                "Content-Type: application/json",
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("9Router cURL error: {$error}");
        }

        if ($httpCode >= 400) {
            throw new \RuntimeException("9Router returned error HTTP {$httpCode}: {$response}");
        }

        $decoded = json_decode($response, true);
        if (!$decoded || !isset($decoded['choices'][0]['message']['content'])) {
            throw new \RuntimeException("Invalid response structure from 9Router: {$response}");
        }

        return [
            'content' => $decoded['choices'][0]['message']['content'],
            'model'   => $decoded['model'] ?? $payload['model'],
            'usage'   => $decoded['usage'] ?? [
                'prompt_tokens'     => 0,
                'completion_tokens' => 0,
                'total_tokens'      => 0,
            ],
            'raw'     => $decoded,
        ];
    }

    /**
     * Stream Chat Completion directly from 9Router and emit SSE chunks
     *
     * @param array $messages
     * @param string|null $model
     * @param callable|null $chunkCallback function($deltaText, $isDone)
     * @return string Full concatenated response text
     */
    public function streamChat(array $messages, ?string $model = null, ?callable $chunkCallback = null): string
    {
        $payload = [
            'model'       => $model ?: 'deepseek-chat',
            'messages'    => $messages,
            'temperature' => 0.7,
            'stream'      => true,
        ];

        $fullContent = '';
        $buffer = '';

        $ch = curl_init("{$this->baseUrl}/chat/completions");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$this->apiKey}",
                "Content-Type: application/json",
            ],
            CURLOPT_WRITEFUNCTION  => function ($curl, $chunk) use (&$fullContent, &$buffer, $chunkCallback) {
                $buffer .= $chunk;
                $lines = explode("\n", $buffer);
                $buffer = array_pop($lines); // Keep remainder in buffer

                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || str_starts_with($line, ':')) {
                        continue;
                    }

                    if (str_starts_with($line, 'data: ')) {
                        $dataStr = substr($line, 6);
                        if ($dataStr === '[DONE]') {
                            if ($chunkCallback) {
                                $chunkCallback('', true);
                            }
                            continue;
                        }

                        $parsed = json_decode($dataStr, true);
                        if ($parsed && isset($parsed['choices'][0]['delta']['content'])) {
                            $delta = $parsed['choices'][0]['delta']['content'];
                            $fullContent .= $delta;
                            if ($chunkCallback) {
                                $chunkCallback($delta, false);
                            }
                        }
                    }
                }

                return strlen($chunk);
            },
        ]);

        curl_exec($ch);
        curl_close($ch);

        return $fullContent;
    }
}
