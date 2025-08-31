<?php

namespace IslamWiki\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use IslamWiki\Core\Container\Container;
use IslamWiki\Core\Cache\FileCache;

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    public function testContainerCanBeInstantiated()
    {
        $this->assertInstanceOf(Container::class, $this->container);
    }

    public function testContainerCanBindAndResolve()
    {
        $this->container->bind('test', function () {
            return 'test_value';
        });

        $result = $this->container->make('test');
        $this->assertEquals('test_value', $result);
    }

    public function testContainerCanBindSingleton()
    {
        $this->container->singleton('singleton', function () {
            return new \stdClass();
        });

        $instance1 = $this->container->make('singleton');
        $instance2 = $this->container->make('singleton');

        $this->assertSame($instance1, $instance2);
    }

    public function testContainerCanBindInstance()
    {
        $instance = new \stdClass();
        $instance->property = 'value';

        $this->container->bind('instance', function() use ($instance) {
            return $instance;
        });

        $resolved = $this->container->make('instance');
        $this->assertSame($instance, $resolved);
        $this->assertEquals('value', $resolved->property);
    }

    public function testContainerThrowsExceptionForUnboundKey()
    {
        $this->expectException(\Exception::class);
        $this->container->make('unbound_key');
    }

    public function testContainerCanCheckIfBound()
    {
        $this->assertFalse($this->container->bound('test_key'));

        $this->container->bind('test_key', function () {
            return 'value';
        });

        $this->assertTrue($this->container->bound('test_key'));
    }

    public function testContainerCanResolveWithParameters()
    {
        $this->container->bind('test_with_params', function () {
            return 'hello_world';
        });

        $result = $this->container->make('test_with_params');
        $this->assertEquals('hello_world', $result);
    }

    public function testContainerCanResolveClass()
    {
        $this->container->bind('cache', FileCache::class);

        $instance = $this->container->make('cache');
        $this->assertInstanceOf(FileCache::class, $instance);
    }

    public function testContainerCanResolveClassWithDependencies()
    {
        $this->container->bind('cache', FileCache::class);
        $this->container->bind('cache_path', function () {
            return 'storage/cache';
        });

        $instance = $this->container->make('cache');
        $this->assertInstanceOf(FileCache::class, $instance);
    }

    public function testContainerCanFlush()
    {
        $this->container->bind('test', function () {
            return 'value';
        });

        $this->assertTrue($this->container->bound('test'));

        $this->container->flush();

        $this->assertFalse($this->container->bound('test'));
    }

    public function testContainerCanResolveArray()
    {
        $this->container->bind('array', function () {
            return ['key' => 'value'];
        });

        $result = $this->container->make('array');
        $this->assertIsArray($result);
        $this->assertEquals('value', $result['key']);
    }

    public function testContainerCanResolveClosure()
    {
        $closure = function () {
            return 'closure_result';
        };

        $this->container->bind('closure', $closure);

        $result = $this->container->make('closure');
        $this->assertEquals('closure_result', $result);
    }
} 