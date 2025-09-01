<?php

namespace IslamWiki\Core;

use IslamWiki\Core\Routing\Router;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Container\Container;
use IslamWiki\Core\Middleware\MiddlewareStack;

/**
 * Main Application Class
 * 
 * This class serves as the core of the IslamWiki framework,
 * handling request processing, middleware execution, and response generation.
 */
class Application
{
    /**
     * @var Container
     */
    protected Container $container;
    
    /**
     * @var Router
     */
    protected Router $router;
    
    /**
     * @var MiddlewareStack
     */
    protected MiddlewareStack $middleware;
    
    /**
     * @var bool
     */
    protected bool $booted = false;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->container = new Container();
        $this->router = new Router();
        $this->middleware = new MiddlewareStack();
        
        $this->registerCoreServices();
        $this->registerRoutes();
    }
    
    /**
     * Handle the incoming HTTP request
     */
    public function handle(): Response
    {
        if (!$this->booted) {
            $this->boot();
        }
        
        // Create request object
        $request = Request::createFromGlobals();
        
        // Run middleware stack
        $request = $this->middleware->process($request);
        
        // Route the request
        $route = $this->router->match($request);
        
        if (!$route) {
            return new Response('Not Found', 404);
        }
        
        // Execute the route handler
        $handler = $route->getHandler();
        $response = $this->container->call($handler, $route->getParameters());
        
        // Ensure we have a Response object
        if (!$response instanceof Response) {
            $response = new Response($response);
        }
        
        return $response;
    }
    
    /**
     * Boot the application
     */
    protected function boot(): void
    {
        // Load configuration
        $this->loadConfiguration();
        
        // Initialize services
        $this->initializeServices();
        
        // Load extensions
        $this->loadExtensions();
        
        $this->booted = true;
    }
    
    /**
     * Register core services in the container
     */
    protected function registerCoreServices(): void
    {
        // Register the application instance
        $this->container->singleton(Application::class, $this);
        
        // Register core services
        $this->container->singleton(Router::class, $this->router);
        $this->container->singleton(MiddlewareStack::class, $this->middleware);
        
        // Register service providers
        $this->registerServiceProviders();
    }
    
    /**
     * Register service providers
     */
    protected function registerServiceProviders(): void
    {
        $providers = [
            \IslamWiki\Providers\DatabaseServiceProvider::class,
            \IslamWiki\Providers\AuthServiceProvider::class,
            \IslamWiki\Providers\CacheServiceProvider::class,
            \IslamWiki\Providers\SecurityServiceProvider::class,
        ];
        
        foreach ($providers as $provider) {
            if (class_exists($provider)) {
                $instance = new $provider($this->container);
                $instance->register();
            }
        }
    }
    
    /**
     * Register application routes
     */
    protected function registerRoutes(): void
    {
        // API routes
        $this->router->group(['prefix' => '/api'], function (Router $router) {
            $router->get('/health', function () {
                return new Response(['status' => 'healthy'], 200, ['Content-Type' => 'application/json']);
            });
        });
        
        // Admin routes
        $this->router->group(['prefix' => '/admin'], function (Router $router) {
            $router->get('/', function () {
                return new Response('Admin Dashboard', 200);
            });
            
            // Load admin database routes
            if (file_exists(__DIR__ . '/../../config/admin_database_routes.php')) {
                $adminDatabaseRoutes = require __DIR__ . '/../../config/admin_database_routes.php';
                foreach ($adminDatabaseRoutes as $route => $config) {
                    $parts = explode(' ', $route);
                    $method = $parts[0];
                    $path = $parts[1];
                    
                    // Remove /admin prefix since we're already in admin group
                    $path = str_replace('/admin', '', $path);
                    
                    switch ($method) {
                        case 'GET':
                            $router->get($path, function () use ($config) {
                                $controllerClass = "\\IslamWiki\\Admin\\" . $config['controller'];
                                if (class_exists($controllerClass)) {
                                    // Create controller with dependencies
                                    if ($controllerClass === "\\IslamWiki\\Admin\\DatabaseController") {
                                        $database = new \IslamWiki\Core\Database\DatabaseManager([
                                            'host' => 'localhost',
                                            'port' => 3306,
                                            'database' => 'islamwiki',
                                            'username' => 'root',
                                            'password' => '',
                                            'timezone' => 'UTC'
                                        ]);
                                        $migrationManager = new \IslamWiki\Core\Database\MigrationManager($database, 'database/migrations/');
                                        $controller = new $controllerClass($database, $migrationManager);
                                    } else {
                                        $controller = new $controllerClass();
                                    }
                                    
                                    $action = $config['action'];
                                    if (method_exists($controller, $action)) {
                                        return $controller->$action();
                                    }
                                }
                                return new Response(['error' => 'Controller or action not found'], 404, ['Content-Type' => 'application/json']);
                            });
                            break;
                        case 'POST':
                            $router->post($path, function () use ($config) {
                                $controllerClass = "\\IslamWiki\\Admin\\" . $config['controller'];
                                if (class_exists($controllerClass)) {
                                    // Create controller with dependencies
                                    if ($controllerClass === "\\IslamWiki\\Admin\\DatabaseController") {
                                        $database = new \IslamWiki\Core\Database\DatabaseManager([
                                            'host' => 'localhost',
                                            'port' => 3306,
                                            'database' => 'islamwiki',
                                            'username' => 'root',
                                            'password' => '',
                                            'timezone' => 'UTC'
                                        ]);
                                        $migrationManager = new \IslamWiki\Core\Database\MigrationManager($database, 'database/migrations/');
                                        $controller = new $controllerClass($database, $migrationManager);
                                    } else {
                                        $controller = new $controllerClass();
                                    }
                                    
                                    $action = $config['action'];
                                    if (method_exists($controller, $action)) {
                                        return $controller->$action();
                                    }
                                }
                                return new Response(['error' => 'Controller or action not found'], 404, ['Content-Type' => 'application/json']);
                            });
                            break;
                    }
                }
            }
        });
        
        // Wiki routes
        $this->router->group(['prefix' => '/wiki'], function (Router $router) {
            $router->get('/', function () {
                return new Response('Wiki Home', 200);
            });
            
            $router->get('/{slug}', function ($slug) {
                return new Response("Wiki Article: {$slug}", 200);
            });
        });
        
        // Default route
        $this->router->get('/', function () {
            return new Response('Welcome to IslamWiki', 200);
        });
    }
    
    /**
     * Load configuration files
     */
    protected function loadConfiguration(): void
    {
        // Configuration loading logic will be implemented here
    }
    
    /**
     * Initialize core services
     */
    protected function initializeServices(): void
    {
        // Service initialization logic will be implemented here
    }
    
    /**
     * Load framework extensions
     */
    protected function loadExtensions(): void
    {
        // Extension loading logic will be implemented here
    }
    
    /**
     * Get the container instance
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
    
    /**
     * Get the router instance
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
    
    /**
     * Get the middleware stack
     */
    public function getMiddleware(): MiddlewareStack
    {
        return $this->middleware;
    }
} 