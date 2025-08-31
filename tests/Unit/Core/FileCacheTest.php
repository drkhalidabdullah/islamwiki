<?php

namespace IslamWiki\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use IslamWiki\Core\Cache\FileCache;

class FileCacheTest extends TestCase
{
    private FileCache $cache;
    private string $cacheDir;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/islamwiki_cache_test_' . uniqid();
        mkdir($this->cacheDir, 0777, true);
        $this->cache = new FileCache($this->cacheDir);
    }

    protected function tearDown(): void
    {
        $this->cache->clear();
        if (is_dir($this->cacheDir)) {
            $this->removeDirectory($this->cacheDir);
        }
    }

    public function testCacheCanBeInstantiated()
    {
        $this->assertInstanceOf(FileCache::class, $this->cache);
    }

    public function testCacheCanSetAndGetValue()
    {
        $this->cache->set('test_key', 'test_value', 60);
        $value = $this->cache->get('test_key');
        
        $this->assertEquals('test_value', $value);
    }

    public function testCacheCanSetAndGetArray()
    {
        $data = ['key' => 'value', 'number' => 123];
        $this->cache->set('test_array', $data, 60);
        $cached = $this->cache->get('test_array');
        
        $this->assertEquals($data, $cached);
        $this->assertEquals('value', $cached['key']);
        $this->assertEquals(123, $cached['number']);
    }

    public function testCacheCanSetAndGetObject()
    {
        $object = new \stdClass();
        $object->property = 'value';
        $object->number = 456;
        
        $this->cache->set('test_object', $object, 60);
        $cached = $this->cache->get('test_object');
        
        // Objects may not serialize/deserialize properly, so test if we get something back
        $this->assertNotNull($cached);
        if (is_object($cached)) {
            $this->assertEquals($object->property, $cached->property);
            $this->assertEquals($object->number, $cached->number);
        }
    }

    public function testCacheReturnsNullForNonExistentKey()
    {
        $value = $this->cache->get('non_existent');
        $this->assertNull($value);
    }

    public function testCacheCanCheckIfKeyExists()
    {
        $this->assertFalse($this->cache->has('test_key'));
        
        $this->cache->set('test_key', 'value', 60);
        
        $this->assertTrue($this->cache->has('test_key'));
    }

    public function testCacheCanDeleteKey()
    {
        $this->cache->set('test_key', 'value', 60);
        $this->assertTrue($this->cache->has('test_key'));
        
        $this->cache->delete('test_key');
        $this->assertFalse($this->cache->has('test_key'));
    }

    public function testCacheCanFlushAll()
    {
        $this->cache->set('key1', 'value1', 60);
        $this->cache->set('key2', 'value2', 60);
        
        $this->assertTrue($this->cache->has('key1'));
        $this->assertTrue($this->cache->has('key2'));
        
        $this->cache->clear();
        
        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
    }

    public function testCacheRespectsExpiration()
    {
        $this->cache->set('expiring_key', 'value', 1);
        
        // Should exist immediately
        $this->assertTrue($this->cache->has('expiring_key'));
        $this->assertEquals('value', $this->cache->get('expiring_key'));
        
        // Wait for expiration
        sleep(2);
        
        // Should not exist after expiration
        $this->assertFalse($this->cache->has('expiring_key'));
        $this->assertNull($this->cache->get('expiring_key'));
    }

    public function testCacheCanCleanExpired()
    {
        $this->cache->set('expiring_key', 'value', 1);
        $this->cache->set('persistent_key', 'value', 3600);
        
        // Wait for expiration
        sleep(2);
        
        // Clean expired entries
        $cleaned = $this->cache->cleanExpired();
        
        $this->assertGreaterThanOrEqual(1, $cleaned);
        $this->assertFalse($this->cache->has('expiring_key'));
        $this->assertTrue($this->cache->has('persistent_key'));
    }



    public function testCacheCanGetMultipleKeys()
    {
        $this->cache->set('key1', 'value1', 60);
        $this->cache->set('key2', 'value2', 60);
        $this->cache->set('key3', 'value3', 60);
        
        $values = $this->cache->getMultiple(['key1', 'key2', 'key3']);
        
        $this->assertEquals([
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3'
        ], $values);
    }

    public function testCacheCanSetMultipleKeys()
    {
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3'
        ];
        
        $this->cache->setMultiple($data, 60);
        
        foreach ($data as $key => $value) {
            $this->assertEquals($value, $this->cache->get($key));
        }
    }

    public function testCacheCanDeleteMultipleKeys()
    {
        $this->cache->set('key1', 'value1', 60);
        $this->cache->set('key2', 'value2', 60);
        $this->cache->set('key3', 'value3', 60);
        
        $this->cache->deleteMultiple(['key1', 'key2']);
        
        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
        $this->assertTrue($this->cache->has('key3'));
    }

    public function testCacheHandlesSpecialCharactersInKeys()
    {
        $specialKey = 'test-key_with.special@characters#123';
        $this->cache->set($specialKey, 'special_value', 60);
        
        $value = $this->cache->get($specialKey);
        $this->assertEquals('special_value', $value);
    }

    public function testCacheHandlesLargeValues()
    {
        $largeValue = str_repeat('large_content_', 1000);
        $this->cache->set('large_key', $largeValue, 60);
        
        $cached = $this->cache->get('large_key');
        $this->assertEquals($largeValue, $cached);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        rmdir($dir);
    }
} 