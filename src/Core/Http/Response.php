<?php

namespace IslamWiki\Core\Http;

/**
 * HTTP Response Class
 * 
 * This class represents an HTTP response and provides methods
 * to set content, status codes, and headers.
 */
class Response
{
    /**
     * @var mixed
     */
    protected $content;
    
    /**
     * @var int
     */
    protected int $statusCode;
    
    /**
     * @var array
     */
    protected array $headers;
    
    /**
     * @var array
     */
    protected array $cookies;
    
    /**
     * Constructor
     */
    public function __construct($content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = array_merge($this->getDefaultHeaders(), $headers);
        $this->cookies = [];
    }
    
    /**
     * Set the response content
     */
    public function setContent($content): self
    {
        $this->content = $content;
        return $this;
    }
    
    /**
     * Get the response content
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * Set the HTTP status code
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }
    
    /**
     * Get the HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    
    /**
     * Set a header
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }
    
    /**
     * Set multiple headers
     */
    public function setHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
        return $this;
    }
    
    /**
     * Get a header value
     */
    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }
    
    /**
     * Get all headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
    
    /**
     * Set a cookie
     */
    public function setCookie(string $name, string $value, int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true): self
    {
        $this->cookies[] = compact('name', 'value', 'expire', 'path', 'domain', 'secure', 'httpOnly');
        return $this;
    }
    
    /**
     * Get all cookies
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }
    
    /**
     * Create a JSON response
     */
    public function json($data, int $statusCode = 200, array $headers = []): self
    {
        $this->content = json_encode($data);
        $this->statusCode = $statusCode;
        $this->setHeaders(array_merge(['Content-Type' => 'application/json'], $headers));
        return $this;
    }
    
    /**
     * Create an HTML response
     */
    public function html(string $content, int $statusCode = 200, array $headers = []): self
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->setHeaders(array_merge(['Content-Type' => 'text/html'], $headers));
        return $this;
    }
    
    /**
     * Create a text response
     */
    public function text(string $content, int $statusCode = 200, array $headers = []): self
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->setHeaders(array_merge(['Content-Type' => 'text/plain'], $headers));
        return $this;
    }
    
    /**
     * Create a redirect response
     */
    public function redirect(string $url, int $statusCode = 302): self
    {
        $this->content = '';
        $this->statusCode = $statusCode;
        $this->setHeader('Location', $url);
        return $this;
    }
    
    /**
     * Set cache headers
     */
    public function setCache(int $maxAge = 3600): self
    {
        $this->setHeaders([
            'Cache-Control' => "public, max-age={$maxAge}",
            'Expires' => gmdate('D, d M Y H:i:s \G\M\T', time() + $maxAge)
        ]);
        return $this;
    }
    
    /**
     * Set no-cache headers
     */
    public function noCache(): self
    {
        $this->setHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
        return $this;
    }
    
    /**
     * Set CORS headers
     */
    public function setCors(string $origin = '*', array $methods = ['GET', 'POST', 'PUT', 'DELETE']): self
    {
        $this->setHeaders([
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Methods' => implode(', ', $methods),
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization'
        ]);
        return $this;
    }
    
    /**
     * Send the response
     */
    public function send(): void
    {
        // Set cookies
        foreach ($this->cookies as $cookie) {
            setcookie(
                $cookie['name'],
                $cookie['value'],
                $cookie['expire'],
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httpOnly']
            );
        }
        
        // Set headers
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
        
        // Set status code
        http_response_code($this->statusCode);
        
        // Output content
        echo $this->content;
    }
    
    /**
     * Get default headers
     */
    protected function getDefaultHeaders(): array
    {
        return [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block'
        ];
    }
} 