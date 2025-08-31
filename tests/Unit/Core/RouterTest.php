<?php

namespace IslamWiki\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use IslamWiki\Core\Routing\Router;
use IslamWiki\Core\Routing\Route;
use IslamWiki\Core\Http\Request;

class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
    }

    public function testRouterCanBeInstantiated()
    {
        $this->assertInstanceOf(Router::class, $this->router);
    }

    public function testRouterCanAddGetRoute()
    {
        $this->router->get('/test', function () {
            return 'test_response';
        });

        $routes = $this->router->getRoutes();
        $this->assertCount(1, $routes['GET']);
        $this->assertEquals('GET', $routes['GET'][0]->getMethods()[0]);
        $this->assertEquals('/test', $routes['GET'][0]->getUri());
    }

    public function testRouterCanAddPostRoute()
    {
        $this->router->post('/test', function () {
            return 'post_response';
        });

        $routes = $this->router->getRoutes();
        $this->assertCount(1, $routes['POST']);
        $this->assertEquals('POST', $routes['POST'][0]->getMethods()[0]);
        $this->assertEquals('/test', $routes['POST'][0]->getUri());
    }

    public function testRouterCanAddPutRoute()
    {
        $this->router->addMatchRoute(['PUT'], '/test', function () {
            return 'put_response';
        });

        $routes = $this->router->getRoutes();
        $this->assertCount(1, $routes['PUT']);
        $this->assertEquals('PUT', $routes['PUT'][0]->getMethods()[0]);
        $this->assertEquals('/test', $routes['PUT'][0]->getUri());
    }

    public function testRouterCanAddDeleteRoute()
    {
        $this->router->addMatchRoute(['DELETE'], '/test', function () {
            return 'delete_response';
        });

        $routes = $this->router->getRoutes();
        $this->assertCount(1, $routes['DELETE']);
        $this->assertEquals('DELETE', $routes['DELETE'][0]->getMethods()[0]);
        $this->assertEquals('/test', $routes['DELETE'][0]->getUri());
    }

    public function testRouterCanAddMultipleRoutes()
    {
        $this->router->get('/users', function () {
            return 'users_list';
        });

        $this->router->post('/users', function () {
            return 'create_user';
        });

        $this->router->get('/users/{id}', function ($id) {
            return "user_{$id}";
        });

        $routes = $this->router->getRoutes();
        $this->assertCount(2, $routes['GET']);
        $this->assertCount(1, $routes['POST']);
    }

    public function testRouterCanAddRouteWithMiddleware()
    {
        $middleware = function ($request, $next) {
            return $next($request);
        };

        $this->router->get('/protected', function () {
            return 'protected_content';
        })->middleware($middleware);

        $routes = $this->router->getRoutes();
        $this->assertCount(1, $routes['GET']);
        $this->assertNotEmpty($routes['GET'][0]->getMiddleware());
    }

    public function testRouterCanAddRouteWithName()
    {
        $this->router->get('/named-route', function () {
            return 'named_response';
        })->name('test.route');

        $routes = $this->router->getRoutes();
        $this->assertCount(1, $routes['GET']);
        $this->assertEquals('test.route', $routes['GET'][0]->getName());
    }

    public function testRouterCanGroupRoutes()
    {
        $this->router->group(['prefix' => 'api', 'middleware' => 'auth'], function ($router) {
            $router->get('/users', function () {
                return 'api_users';
            });
        });

        $routes = $this->router->getRoutes();
        $this->assertCount(1, $routes['GET']);
        $this->assertEquals('api/users', $routes['GET'][0]->getUri());
    }

    public function testRouterCanMatchExactPath()
    {
        $this->router->get('/exact', function () {
            return 'exact_match';
        });

        $request = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/exact']);

        $route = $this->router->match($request);
        $this->assertNotNull($route);
        $this->assertEquals('/exact', $route->getUri());
    }

    public function testRouterCanMatchPathWithParameters()
    {
        $this->router->get('/users/{id}', function ($id) {
            return "user_{$id}";
        });

        $request = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/users/123']);

        $route = $this->router->match($request);
        $this->assertNotNull($route);
        $this->assertEquals('/users/{id}', $route->getUri());
    }

    public function testRouterReturnsNullForNoMatch()
    {
        $this->router->get('/test', function () {
            return 'test';
        });

        $request = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/nonexistent']);

        $route = $this->router->match($request);
        $this->assertNull($route);
    }

    public function testRouterCanHandleMethodMismatch()
    {
        $this->router->get('/test', function () {
            return 'test';
        });

        $request = new Request([], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/test']);

        $route = $this->router->match($request);
        $this->assertNull($route);
    }

    public function testRouterCanGetRouteByName()
    {
        $this->router->get('/test', function () {
            return 'test';
        })->name('test.route');

        $routes = $this->router->getRoutes();
        $namedRoute = null;
        foreach ($routes['GET'] ?? [] as $route) {
            if ($route->getName() === 'test.route') {
                $namedRoute = $route;
                break;
            }
        }
        
        $this->assertNotNull($namedRoute);
        $this->assertEquals('test.route', $namedRoute->getName());
    }

    public function testRouterReturnsNullForNonExistentRouteName()
    {
        $routes = $this->router->getRoutes();
        $namedRoute = null;
        foreach ($routes['GET'] ?? [] as $route) {
            if ($route->getName() === 'non.existent') {
                $namedRoute = $route;
                break;
            }
        }
        
        $this->assertNull($namedRoute);
    }
} 