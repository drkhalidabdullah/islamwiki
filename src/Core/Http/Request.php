<?php

namespace IslamWiki\Core\Http;

/**
 * HTTP Request Class
 * 
 * This class represents an HTTP request and provides methods
 * to access request data, headers, and parameters.
 */
class Request
{
    /**
     * @var array
     */
    protected array $get;
    
    /**
     * @var array
     */
    protected array $post;
    
    /**
     * @var array
     */
    protected array $server;
    
    /**
     * @var array
     */
    protected array $files;
    
    /**
     * @var array
     */
    protected array $cookies;
    
    /**
     * @var string
     */
    protected string $method;
    
    /**
     * @var string
     */
    protected string $uri;
    
    /**
     * @var string
     */
    protected string $queryString;
    
    /**
     * @var array
     */
    protected array $headers;
    
    /**
     * Create a new request instance from global variables
     */
    public static function createFromGlobals(): self
    {
        return new static(
            $_GET ?? [],
            $_POST ?? [],
            $_SERVER ?? [],
            $_FILES ?? [],
            $_COOKIE ?? []
        );
    }
    
    /**
     * Constructor
     */
    public function __construct(array $get = [], array $post = [], array $server = [], array $files = [], array $cookies = [])
    {
        $this->get = $get;
        $this->post = $post;
        $this->server = $server;
        $this->files = $files;
        $this->cookies = $cookies;
        
        $this->method = $this->server['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $this->server['REQUEST_URI'] ?? '/';
        $this->queryString = $this->server['QUERY_STRING'] ?? '';
        $this->headers = $this->getHeaders();
    }
    
    /**
     * Get the request method
     */
    public function getMethod(): string
    {
        return strtoupper($this->method);
    }
    
    /**
     * Check if the request method matches the given method
     */
    public function isMethod(string $method): bool
    {
        return $this->getMethod() === strtoupper($method);
    }
    
    /**
     * Check if the request is a GET request
     */
    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }
    
    /**
     * Check if the request is a POST request
     */
    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }
    
    /**
     * Check if the request is a PUT request
     */
    public function isPut(): bool
    {
        return $this->isMethod('PUT');
    }
    
    /**
     * Check if the request is a DELETE request
     */
    public function isDelete(): bool
    {
        return $this->isMethod('DELETE');
    }
    
    /**
     * Get the request URI
     */
    public function getUri(): string
    {
        return $this->uri;
    }
    
    /**
     * Get the query string
     */
    public function getQueryString(): string
    {
        return $this->queryString;
    }
    
    /**
     * Get a GET parameter
     */
    public function get(string $key, $default = null)
    {
        return $this->get[$key] ?? $default;
    }
    
    /**
     * Get all GET parameters
     */
    public function allGet(): array
    {
        return $this->get;
    }
    
    /**
     * Get a POST parameter
     */
    public function post(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }
    
    /**
     * Get all POST parameters
     */
    public function allPost(): array
    {
        return $this->post;
    }
    
    /**
     * Get a parameter from GET or POST
     */
    public function input(string $key, $default = null)
    {
        return $this->post($key, $this->get($key, $default));
    }
    
    /**
     * Get all input parameters
     */
    public function allInput(): array
    {
        return array_merge($this->get, $this->post);
    }
    
    /**
     * Get a file upload
     */
    public function file(string $key)
    {
        return $this->files[$key] ?? null;
    }
    
    /**
     * Get all file uploads
     */
    public function allFiles(): array
    {
        return $this->files;
    }
    
    /**
     * Get a cookie
     */
    public function cookie(string $key, $default = null)
    {
        return $this->cookies[$key] ?? $default;
    }
    
    /**
     * Get all cookies
     */
    public function allCookies(): array
    {
        return $this->cookies;
    }
    
    /**
     * Get a header value
     */
    public function header(string $key, $default = null)
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$key] ?? $default;
    }
    
    /**
     * Get all headers
     */
    public function allHeaders(): array
    {
        return $this->headers;
    }
    
    /**
     * Check if the request is AJAX
     */
    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }
    
    /**
     * Check if the request expects JSON
     */
    public function expectsJson(): bool
    {
        return $this->header('Accept') && strpos($this->header('Accept'), 'application/json') !== false;
    }
    
    /**
     * Get the request path
     */
    public function getPath(): string
    {
        $uri = parse_url($this->uri, PHP_URL_PATH);
        return $uri ?: '/';
    }
    
    /**
     * Get the request URL
     */
    public function getUrl(): string
    {
        $scheme = $this->server['HTTPS'] ?? 'off';
        $scheme = $scheme === 'on' ? 'https' : 'http';
        $host = $this->server['HTTP_HOST'] ?? 'localhost';
        
        return $scheme . '://' . $host . $this->uri;
    }
    
    /**
     * Get the base URL
     */
    public function getBaseUrl(): string
    {
        $scheme = $this->server['HTTPS'] ?? 'off';
        $scheme = $scheme === 'on' ? 'https' : 'http';
        $host = $this->server['HTTP_HOST'] ?? 'localhost';
        
        return $scheme . '://' . $host;
    }
    
    /**
     * Get headers from server variables
     */
    protected function getHeaders(): array
    {
        $headers = [];
        
        foreach ($this->server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$header] = $value;
            }
        }
        
        return $headers;
    }
} 