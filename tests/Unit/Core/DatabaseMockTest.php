<?php

namespace IslamWiki\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use IslamWiki\Core\Database\Database;

class DatabaseMockTest extends TestCase
{
    public function testDatabaseClassExists()
    {
        $this->assertTrue(class_exists(Database::class));
    }

    public function testDatabaseHasRequiredMethods()
    {
        $reflection = new \ReflectionClass(Database::class);
        
        $requiredMethods = [
            'prepare',
            'query',
            'lastInsertId',
            'beginTransaction',
            'commit',
            'rollback',
            'inTransaction',
            'getConnection',
            'close'
        ];
        
        foreach ($requiredMethods as $method) {
            $this->assertTrue(
                $reflection->hasMethod($method),
                "Database class should have method: {$method}"
            );
        }
    }

    public function testDatabaseConstructorAcceptsConfig()
    {
        $reflection = new \ReflectionClass(Database::class);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor);
        $this->assertCount(1, $constructor->getParameters());
        
        $param = $constructor->getParameters()[0];
        $this->assertEquals('config', $param->getName());
        $this->assertTrue($param->getType()->isBuiltin());
        $this->assertEquals('array', $param->getType()->getName());
    }

    public function testDatabaseMethodsReturnCorrectTypes()
    {
        $reflection = new \ReflectionClass(Database::class);
        
        // Test method return types
        $methodReturnTypes = [
            'prepare' => 'PDOStatement',
            'query' => 'PDOStatement',
            'lastInsertId' => 'string',
            'beginTransaction' => 'bool',
            'commit' => 'bool',
            'rollback' => 'bool',
            'inTransaction' => 'bool',
            'getConnection' => 'PDO',
            'close' => 'void'
        ];
        
        foreach ($methodReturnTypes as $method => $expectedType) {
            if ($reflection->hasMethod($method)) {
                $methodReflection = $reflection->getMethod($method);
                $returnType = $methodReflection->getReturnType();
                
                if ($returnType) {
                    $actualType = $returnType->getName();
                    $this->assertEquals(
                        $expectedType,
                        $actualType,
                        "Method {$method} should return {$expectedType}, got {$actualType}"
                    );
                }
            }
        }
    }

    public function testDatabaseHasPrivateConnectionProperty()
    {
        $reflection = new \ReflectionClass(Database::class);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PRIVATE);
        
        $hasConnectionProperty = false;
        foreach ($properties as $property) {
            if ($property->getName() === 'connection') {
                $hasConnectionProperty = true;
                break;
            }
        }
        
        $this->assertTrue($hasConnectionProperty, 'Database should have private connection property');
    }

    public function testDatabaseHasPrivateConfigProperty()
    {
        $reflection = new \ReflectionClass(Database::class);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PRIVATE);
        
        $hasConfigProperty = false;
        foreach ($properties as $property) {
            if ($property->getName() === 'config') {
                $hasConfigProperty = true;
                break;
            }
        }
        
        $this->assertTrue($hasConfigProperty, 'Database should have private config property');
    }

    public function testDatabaseHasPrivateConnectMethod()
    {
        $reflection = new \ReflectionClass(Database::class);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PRIVATE);
        
        $hasConnectMethod = false;
        foreach ($methods as $method) {
            if ($method->getName() === 'connect') {
                $hasConnectMethod = true;
                break;
            }
        }
        
        $this->assertTrue($hasConnectMethod, 'Database should have private connect method');
    }

    public function testDatabaseExceptionClassExists()
    {
        $this->assertTrue(class_exists('IslamWiki\Core\Database\DatabaseException'));
    }

    public function testDatabaseExceptionExtendsException()
    {
        $reflection = new \ReflectionClass('IslamWiki\Core\Database\DatabaseException');
        $this->assertTrue($reflection->isSubclassOf(\Exception::class));
    }
} 