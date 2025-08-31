<?php

namespace IslamWiki\Core\Routing;

/**
 * Route Class
 * 
 * This class represents a single route in the IslamWiki framework,
 * containing HTTP methods, URI pattern, handler, and middleware.
 */
class Route
{
    /**
     * @var array
     */
    protected array $methods;
    
    /**
     * @var string
     */
    protected string $uri;
    
    /**
     * @var mixed
     */
    protected $handler;
    
    /**
     * @var array
     */
    protected array $middleware = [];
    
    /**
     * @var array
     */
    protected array $parameters = [];
    
    /**
     * @var string|null
     */
    protected ?string $name = null;
    
    /**
     * Constructor
     */
    public function __construct(array $methods, string $uri, $handler)
    {
        $this->methods = $methods;
        $this->uri = $uri;
        $this->handler = $handler;
    }
    
    /**
     * Get the HTTP methods this route accepts
     */
    public function getMethods(): array
    {
        return $this->methods;
    }
    
    /**
     * Get the URI pattern
     */
    public function getUri(): string
    {
        return $this->uri;
    }
    
    /**
     * Get the route handler
     */
    public function getHandler()
    {
        return $this->handler;
    }
    
    /**
     * Get the middleware stack
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }
    
    /**
     * Get the route parameters
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
    
    /**
     * Get the route name
     */
    public function getName(): ?string
    {
        return $this->name;
    }
    
    /**
     * Set the route name
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Add middleware to the route
     */
    public function middleware($middleware): self
    {
        if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        } else {
            $this->middleware[] = $middleware;
        }
        
        return $this;
    }
    
    /**
     * Check if the route matches the given path
     */
    public function matches(string $path): bool
    {
        $pattern = $this->uriToRegex();
        return preg_match($pattern, $path, $matches) === 1;
    }
    
    /**
     * Convert URI pattern to regex
     */
    protected function uriToRegex(): string
    {
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $this->uri);
        $pattern = str_replace('/', '\/', $pattern);
        return '/^' . $pattern . '$/';
    }
    
    /**
     * Extract parameters from the path
     */
    public function extractParameters(string $path): array
    {
        $pattern = $this->uriToRegex();
        if (preg_match($pattern, $path, $matches)) {
            array_shift($matches); // Remove the full match
            
            // Extract parameter names from the URI
            preg_match_all('/\{([^}]+)\}/', $this->uri, $paramNames);
            $paramNames = $paramNames[1];
            
            $parameters = [];
            foreach ($paramNames as $index => $name) {
                if (isset($matches[$index])) {
                    $parameters[$name] = $matches[$index];
                }
            }
            
            $this->parameters = $parameters;
            return $parameters;
        }
        
        return [];
    }
    
    /**
     * Check if the route has a specific parameter
     */
    public function hasParameter(string $name): bool
    {
        return isset($this->parameters[$name]);
    }
    
    /**
     * Get a parameter value
     */
    public function getParameter(string $name, $default = null)
    {
        return $this->parameters[$name] ?? $default;
    }
    
    /**
     * Set a parameter value
     */
    public function setParameter(string $name, $value): self
    {
        $this->parameters[$name] = $value;
        return $this;
    }
    
    /**
     * Check if the route accepts a specific HTTP method
     */
    public function acceptsMethod(string $method): bool
    {
        return in_array(strtoupper($method), $this->methods);
    }
    
    /**
     * Check if the route accepts any method
     */
    public function acceptsAnyMethod(): bool
    {
        return in_array('*', $this->methods);
    }
    
    /**
     * Convert route to string representation
     */
    public function __toString(): string
    {
        $methods = implode('|', $this->methods);
        return "{$methods} {$this->uri}";
    }
} 