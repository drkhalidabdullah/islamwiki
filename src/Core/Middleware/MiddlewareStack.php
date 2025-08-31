<?php

namespace IslamWiki\Core\Middleware;

use IslamWiki\Core\Http\Request;

/**
 * Middleware Stack Class
 * 
 * This class manages the execution of middleware for request processing
 * in the IslamWiki framework.
 */
class MiddlewareStack
{
    /**
     * @var array
     */
    protected array $middleware = [];
    
    /**
     * @var array
     */
    protected array $globalMiddleware = [];
    
    /**
     * Add middleware to the stack
     */
    public function add($middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }
    
    /**
     * Add global middleware
     */
    public function addGlobal($middleware): self
    {
        $this->globalMiddleware[] = $middleware;
        return $this;
    }
    
    /**
     * Process the request through the middleware stack
     */
    public function process(Request $request): Request
    {
        $pipeline = $this->createPipeline();
        return $pipeline($request);
    }
    
    /**
     * Create the middleware pipeline
     */
    protected function createPipeline(): callable
    {
        $pipeline = function (Request $request) {
            return $request;
        };
        
        // Add global middleware first
        foreach (array_reverse($this->globalMiddleware) as $middleware) {
            $pipeline = $this->wrapMiddleware($middleware, $pipeline);
        }
        
        // Add route-specific middleware
        foreach (array_reverse($this->middleware) as $middleware) {
            $pipeline = $this->wrapMiddleware($middleware, $pipeline);
        }
        
        return $pipeline;
    }
    
    /**
     * Wrap middleware in the pipeline
     */
    protected function wrapMiddleware($middleware, callable $next): callable
    {
        return function (Request $request) use ($middleware, $next) {
            return $this->executeMiddleware($middleware, $request, $next);
        };
    }
    
    /**
     * Execute a single middleware
     */
    protected function executeMiddleware($middleware, Request $request, callable $next)
    {
        if (is_string($middleware)) {
            $middleware = $this->resolveMiddleware($middleware);
        }
        
        if (is_callable($middleware)) {
            return $middleware($request, $next);
        }
        
        if (is_object($middleware) && method_exists($middleware, 'handle')) {
            return $middleware->handle($request, $next);
        }
        
        throw new \InvalidArgumentException('Invalid middleware: ' . gettype($middleware));
    }
    
    /**
     * Resolve middleware from string
     */
    protected function resolveMiddleware(string $middleware)
    {
        if (class_exists($middleware)) {
            return new $middleware();
        }
        
        throw new \InvalidArgumentException("Middleware class not found: {$middleware}");
    }
    
    /**
     * Get all middleware
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }
    
    /**
     * Get global middleware
     */
    public function getGlobalMiddleware(): array
    {
        return $this->globalMiddleware;
    }
    
    /**
     * Clear all middleware
     */
    public function clear(): void
    {
        $this->middleware = [];
        $this->globalMiddleware = [];
    }
    
    /**
     * Check if middleware stack is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->middleware) && empty($this->globalMiddleware);
    }
    
    /**
     * Get the count of middleware
     */
    public function count(): int
    {
        return count($this->middleware) + count($this->globalMiddleware);
    }
} 