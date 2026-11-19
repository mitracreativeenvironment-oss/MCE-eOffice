# PANDUAN SETUP & INTEGRASI: 9ROUTER DENGAN CODEIGNITER 4 (KANTOR 3D)
## Menghubungkan Backend PHP dengan Smart AI Gateway di MCE-eOffice

Panduan ini mendokumentasikan setup arsitektur ramping:
**Vue 3 (Three.js 3D) ⟷ CodeIgniter 4 (Full REST API + MySQL) ⟷ 9Router (Central AI Gateway)**

---

## 1. Menjalankan 9Router (`decolua/9router`)

9Router telah terpasang secara global di sistem (`v0.5.119`).

### Menjalankan 9Router:
Buka terminal baru atau jalankan command:
```bash
9router
```
Secara default, 9Router akan berjalan dan mendengarkan di:
- **API Endpoint:** `http://127.0.0.1:20128/v1`
- **Web Dashboard:** `http://localhost:20128`

Di Web Dashboard 9Router, masukkan API Key untuk provider yang ingin Anda gunakan (Anthropic, DeepSeek, OpenAI, Gemini, dll).

---

## 2. Konfigurasi CI4 untuk Menghubungi 9Router

Di proyek CodeIgniter 4, buat file konfigurasi di `app/Config/RouterGateway.php`:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class RouterGateway extends BaseConfig
{
    /**
     * Base URL 9Router (OpenAI-compatible)
     */
    public string $baseUrl = 'http://127.0.0.1:20128/v1';

    /**
     * Internal API Key (jika disetel di 9Router)
     */
    public string $apiKey = '9router-internal-key';

    /**
     * Timeout request (detik)
     */
    public int $timeout = 60;

    /**
     * Pemetaan Model Default per Tier
     */
    public array $modelTiers = [
        'high_reasoning' => 'claude-3-7-sonnet',
        'coding'         => 'deepseek-chat',
        'fast_triage'    => 'gemini-2.5-flash',
    ];
}
```

---

## 3. RouterService di CodeIgniter 4 (`app/Services/RouterService.php`)

Service ini bertanggung jawab mengirim prompt agen ke 9Router dengan dukungan streaming SSE:

```php
<?php

namespace App\Services;

use Config\RouterGateway;

class RouterService
{
    protected RouterGateway $config;

    public function __construct()
    {
        $this->config = config('RouterGateway');
    }

    /**
     * Mengirim percakapan ke 9Router
     */
    public function sendChat(array $messages, string $tier = 'coding', bool $stream = false)
    {
        $model = $this->config->modelTiers[$tier] ?? 'deepseek-chat';
        $payload = [
            'model'       => $model,
            'messages'    => $messages,
            'stream'      => $stream,
            'temperature' => 0.2,
        ];

        $ch = curl_init("{$this->config->baseUrl}/chat/completions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer {$this->config->apiKey}",
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config->timeout);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            log_message('error', "9Router Error ($httpCode): $response");
            return null;
        }

        return json_decode($response, true);
    }
}
```

---

## 4. Keuntungan Alur Ramping Ini
1. **Sangat Cepat:** Tidak ada perantara middleware yang menambah jeda waktu (latensi).
2. **Mudah Dirawat:** Cukup PHP XAMPP + 9Router (Node.js).
3. **Failover Otomatis:** Jika salah satu provider AI down atau terkena limit rate 429, 9Router langsung memindahkan ke model cadangan tanpa mengganggu percakapan avatar 3D pengguna.

