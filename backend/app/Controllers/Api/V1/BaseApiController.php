<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class BaseApiController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var IncomingRequest|CLIRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    protected $format = 'json';

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Apply CORS headers to responses
        $this->applyCorsHeaders();
    }

    /**
     * Parse JSON body safely without throwing on malformed or empty payloads.
     */
    protected function getBody(): array
    {
        try {
            $body = $this->request->getJSON(true);
            if (is_array($body)) {
                return $body;
            }
        } catch (\Throwable $e) {
            // fallback
        }

        $raw = $this->request->getBody();
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $post = $this->request->getPost();
        return is_array($post) ? $post : [];
    }

    /**
     * Success JSON response envelope
     */
    protected function success(mixed $data = null, string $message = 'OK', int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
            'errors'  => null,
        ]);
    }

    /**
     * Created response (201)
     */
    protected function created(mixed $data = null, string $message = 'Created.'): ResponseInterface
    {
        return $this->success($data, $message, 201);
    }

    /**
     * No content response (204)
     */
    protected function noContent(): ResponseInterface
    {
        return $this->response->setStatusCode(204);
    }

    /**
     * Generic error response
     */
    protected function error(string $message, int $status = 400, mixed $errors = null): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON([
            'status'  => 'error',
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ]);
    }

    /**
     * Validation error (422)
     */
    protected function validationError(array $errors, string $message = 'Validation failed.'): ResponseInterface
    {
        return $this->error($message, 422, $errors);
    }

    /**
     * Not found error (404)
     */
    protected function notFound(string $message = 'Resource not found.'): ResponseInterface
    {
        return $this->error($message, 404);
    }

    /**
     * Server error (500)
     */
    protected function serverError(string $message = 'Internal server error.'): ResponseInterface
    {
        return $this->error($message, 500);
    }

    /**
     * Set permissive CORS headers for local development and Vue 3 frontend
     */
    protected function applyCorsHeaders(): void
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE, PATCH');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }

    /**
     * Handle preflight OPTIONS requests cleanly
     */
    public function options(): ResponseInterface
    {
        return $this->response->setStatusCode(204);
    }
}
