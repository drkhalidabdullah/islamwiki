<?php

namespace IslamWiki\Core\Container;

use Closure;
use ReflectionClass;
use ReflectionParameter;
use InvalidArgumentException;

/**
 * Simple Dependency Injection Container
 * 
 * This class provides basic dependency injection capabilities
 * for the IslamWiki framework.
 */
class Container
{
    /**
     * @var array
     */
    protected array $bindings = [];
    
    /**
     * @var array
     */
    protected array $singletons = [];
    
    /**
     * @var array
     */
    protected array $instances = [];
    
    /**
     * Bind a class or interface to a concrete implementation
     */
    public function bind(string $abstract, $concrete = null, bool $shared = false): void
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }
        
        $this->bindings[$abstract] = compact('concrete', 'shared');
    }
    
    /**
     * Bind a singleton
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }
    
    /**
     * Resolve a class from the container
     */
    public function make(string $abstract)
    {
        // Check if we already have an instance
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        
        // Check if we have a binding
        if (isset($this->bindings[$abstract])) {
            $binding = $this->bindings[$abstract];
            $concrete = $binding['concrete'];
            
            if ($concrete instanceof Closure) {
                $instance = $concrete($this);
            } else {
                $instance = $this->build($concrete);
            }
            
            if ($binding['shared']) {
                $this->instances[$abstract] = $instance;
            }
            
            return $instance;
        }
        
        // Try to build the class directly
        return $this->build($abstract);
    }
    
    /**
     * Build a concrete instance
     */
    protected function build(string $concrete)
    {
        try {
            $reflector = new ReflectionClass($concrete);
        } catch (\ReflectionException $e) {
            throw new InvalidArgumentException("Target class [{$concrete}] does not exist.");
        }
        
        if (!$reflector->isInstantiable()) {
            throw new InvalidArgumentException("Target [{$concrete}] is not instantiable.");
        }
        
        $constructor = $reflector->getConstructor();
        
        if (is_null($constructor)) {
            return new $concrete;
        }
        
        $dependencies = $this->resolveDependencies($constructor->getParameters());
        
        return $reflector->newInstanceArgs($dependencies);
    }
    
    /**
     * Resolve dependencies for a method
     */
    protected function resolveDependencies(array $dependencies): array
    {
        $results = [];
        
        foreach ($dependencies as $dependency) {
            $results[] = $this->resolveDependency($dependency);
        }
        
        return $results;
    }
    
    /**
     * Resolve a single dependency
     */
    protected function resolveDependency(ReflectionParameter $dependency)
    {
        if ($dependency->isDefaultValueAvailable()) {
            return $dependency->getDefaultValue();
        }
        
        if ($dependency->getType() && !$dependency->getType()->isBuiltin()) {
            $type = $dependency->getType()->getName();
            return $this->make($type);
        }
        
        throw new InvalidArgumentException("Unresolvable dependency [{$dependency->getName()}]");
    }
    
    /**
     * Call a method with dependencies
     */
    public function call($callback, array $parameters = [])
    {
        if (is_string($callback)) {
            $callback = $this->make($callback);
        }
        
        if (is_array($callback)) {
            $callback = [$this->make($callback[0]), $callback[1]];
        }
        
        return call_user_func_array($callback, $parameters);
    }
    
    /**
     * Check if a binding exists
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }
    
    /**
     * Get all bindings
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }
    
    /**
     * Get all instances
     */
    public function getInstances(): array
    {
        return $this->instances;
    }
    
    /**
     * Clear all bindings and instances
     */
    public function flush(): void
    {
        $this->bindings = [];
        $this->instances = [];
    }
} 