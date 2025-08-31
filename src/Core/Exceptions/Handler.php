<?php

namespace IslamWiki\Core\Exceptions;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

/**
 * Exception Handler
 * 
 * @author Khalid Abdullah
 * @version 0.0.1
 * @date 2025-08-30
 * @license AGPL-3.0
 */
class Handler
{
    private bool $debug;
    private string $logPath;

    public function __construct(bool $debug = false, string $logPath = 'storage/logs')
    {
        $this->debug = $debug;
        $this->logPath = $logPath;
        
        // Ensure log directory exists
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    /**
     * Handle an exception
     */
    public function handle(\Throwable $exception, Request $request = null): Response
    {
        // Log the exception
        $this->logException($exception, $request);

        // Return appropriate response based on request type
        if ($request && $request->expectsJson()) {
            return $this->renderJsonException($exception);
        }

        return $this->renderHtmlException($exception);
    }

    /**
     * Log exception to file
     */
    private function logException(\Throwable $exception, Request $request = null): void
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => 'ERROR',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'request' => $request ? [
                'method' => $request->getMethod(),
                'uri' => $request->getUri(),
                'ip' => $request->getClientIp(),
                'user_agent' => $request->getUserAgent()
            ] : null
        ];

        $logFile = $this->logPath . '/errors-' . date('Y-m-d') . '.log';
        $logMessage = json_encode($logEntry, JSON_PRETTY_PRINT) . "\n---\n";
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Render JSON exception response
     */
    private function renderJsonException(\Throwable $exception): Response
    {
        $data = [
            'success' => false,
            'error' => 'Internal Server Error',
            'message' => $this->debug ? $exception->getMessage() : 'An error occurred'
        ];

        if ($this->debug) {
            $data['debug'] = [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace()
            ];
        }

        return (new Response())->json($data, 500);
    }

    /**
     * Render HTML exception response
     */
    private function renderHtmlException(\Throwable $exception): Response
    {
        $statusCode = 500;
        $title = 'Internal Server Error';
        $message = $this->debug ? $exception->getMessage() : 'An error occurred while processing your request.';

        if ($this->debug) {
            $html = $this->renderDebugHtml($exception, $title, $message);
        } else {
            $html = $this->renderProductionHtml($title, $message);
        }

        return new Response($html, $statusCode, ['Content-Type' => 'text/html']);
    }

    /**
     * Render debug HTML for development
     */
    private function renderDebugHtml(\Throwable $exception, string $title, string $message): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <title>{$title}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
                .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .error-title { color: #d32f2f; font-size: 24px; margin-bottom: 20px; }
                .error-message { color: #333; margin-bottom: 20px; }
                .error-details { background: #f8f9fa; padding: 20px; border-radius: 4px; font-family: monospace; }
                .file-info { color: #666; font-size: 14px; margin-bottom: 10px; }
                .trace { white-space: pre-wrap; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='error-title'>{$title}</div>
                <div class='error-message'>{$message}</div>
                <div class='error-details'>
                    <div class='file-info'>File: {$exception->getFile()}:{$exception->getLine()}</div>
                    <div class='trace'>{$exception->getTraceAsString()}</div>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Render production HTML (no sensitive information)
     */
    private function renderProductionHtml(string $title, string $message): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <title>{$title}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
                .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
                .error-title { color: #d32f2f; font-size: 24px; margin-bottom: 20px; }
                .error-message { color: #333; margin-bottom: 20px; }
                .home-link { color: #1976d2; text-decoration: none; }
                .home-link:hover { text-decoration: underline; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='error-title'>{$title}</div>
                <div class='error-message'>{$message}</div>
                <p><a href='/' class='home-link'>Return to Home</a></p>
            </div>
        </body>
        </html>";
    }

    /**
     * Set debug mode
     */
    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * Set log path
     */
    public function setLogPath(string $logPath): self
    {
        $this->logPath = $logPath;
        return $this;
    }
} 