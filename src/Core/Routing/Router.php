<?php

namespace IslamWiki\Core\Routing;

use IslamWiki\Core\Http\Request;

/**
 * Router Class
 * 
 * This class handles URL routing for the IslamWiki framework,
 * supporting various HTTP methods, route groups, and parameters.
 */
class Router
{
    /**
     * @var array
     */
    protected array $routes = [];
    
    /**
     * @var string
     */
    protected string $currentGroupPrefix = '';
    
    /**
     * @var array
     */
    protected array $currentGroupMiddleware = [];
    
    /**
     * Register a GET route
     */
    public function get(string $uri, $handler): Route
    {
        return $this->addRoute(['GET'], $uri, $handler);
    }
    
    /**
     * Register a POST route
     */
    public function post(string $uri, $handler): Route
    {
        return $this->addRoute(['POST'], $uri, $handler);
    }
    
    /**
     * Register a PUT route
     */
    public function put(string $uri, $handler): Route
    {
        return $this->addRoute(['PUT'], $uri, $handler);
    }
    
    /**
     * Register a DELETE route
     */
    public function delete(string $uri, $handler): Route
    {
        return $this->addRoute(['DELETE'], $uri, $handler);
    }
    
    /**
     * Register a route that accepts multiple methods
     */
    public function addMatchRoute(array $methods, string $uri, $handler): Route
    {
        return $this->addRoute($methods, $uri, $handler);
    }
    
    /**
     * Register a route that accepts any method
     */
    public function any(string $uri, $handler): Route
    {
        return $this->addRoute(['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'], $uri, $handler);
    }
    
    /**
     * Create a route group
     */
    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix = $this->currentGroupPrefix;
        $previousMiddleware = $this->currentGroupMiddleware;
        
        if (isset($attributes['prefix'])) {
            $this->currentGroupPrefix .= $attributes['prefix'];
        }
        
        if (isset($attributes['middleware'])) {
            $this->currentGroupMiddleware = array_merge(
                $this->currentGroupMiddleware,
                (array) $attributes['middleware']
            );
        }
        
        $callback($this);
        
        $this->currentGroupPrefix = $previousPrefix;
        $this->currentGroupMiddleware = $previousMiddleware;
    }
    
    /**
     * Add a route to the collection
     */
    protected function addRoute(array $methods, string $uri, $handler): Route
    {
        $uri = $this->currentGroupPrefix . $uri;
        
        $route = new Route($methods, $uri, $handler);
        
        if (!empty($this->currentGroupMiddleware)) {
            $route->middleware($this->currentGroupMiddleware);
        }
        
        foreach ($methods as $method) {
            $this->routes[$method][] = $route;
        }
        
        return $route;
    }
    
    /**
     * Match a request to a route
     */
    public function match(Request $request): ?Route
    {
        $method = $request->getMethod();
        $path = $request->getPath();
        
        if (!isset($this->routes[$method])) {
            return null;
        }
        
        foreach ($this->routes[$method] as $route) {
            if ($route->matches($path)) {
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Get all routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
    
    /**
     * Get routes by method
     */
    public function getRoutesByMethod(string $method): array
    {
        return $this->routes[$method] ?? [];
    }
    
    /**
     * Clear all routes
     */
    public function clear(): void
    {
        $this->routes = [];
    }
} 