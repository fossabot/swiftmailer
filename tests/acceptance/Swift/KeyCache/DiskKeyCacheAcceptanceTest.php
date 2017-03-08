<?php

/**
 * Class Swift_KeyCache_DiskKeyCacheAcceptanceTest
 */
class Swift_KeyCache_DiskKeyCacheAcceptanceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Swift_KeyCache_DiskKeyCache
     */
    private $_cache;

    /**
     * @var string
     */
    private $_key1;

    /**
     * @var string
     */
    private $_key2;

    protected function setUp()
    {
        $this->_key1 = uniqid(microtime(true), true);
        $this->_key2 = uniqid(microtime(true), true);
        $this->_cache = new Swift_KeyCache_DiskKeyCache(
            new Swift_KeyCache_SimpleKeyCacheInputStream(),
            sys_get_temp_dir()
            );
    }

    public function testStringDataCanBeSetAndFetched()
    {
        $this->_cache->setString($this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE);
        $this->assertSame('test', $this->_cache->getString($this->_key1, 'foo'));
    }

    public function testStringDataCanBeOverwritten()
    {
        $this->_cache->setString($this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE);
        $this->_cache->setString($this->_key1, 'foo', 'whatever', Swift_KeyCache::MODE_WRITE);
        $this->assertSame('whatever', $this->_cache->getString($this->_key1, 'foo'));
    }

    public function testStringDataCanBeAppended()
    {
        $this->_cache->setString($this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE);
        $this->_cache->setString($this->_key1, 'foo', 'ing', Swift_KeyCache::MODE_APPEND);
        $this->assertSame('testing', $this->_cache->getString($this->_key1, 'foo'));
    }

    public function testHasKeyReturnValue()
    {
        $this->assertFalse($this->_cache->hasKey($this->_key1, 'foo'));
        $this->_cache->setString($this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE);
        $this->assertTrue($this->_cache->hasKey($this->_key1, 'foo'));
    }

    public function testNsKeyIsWellPartitioned()
    {
        $this->_cache->setString($this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE);
        $this->_cache->setString($this->_key2, 'foo', 'ing', Swift_KeyCache::MODE_WRITE);
        $this->assertSame('test', $this->_cache->getString($this->_key1, 'foo'));
        $this->assertSame('ing', $this->_cache->getString($this->_key2, 'foo'));
    }

    public function testItemKeyIsWellPartitioned()
    {
        $this->_cache->setString($this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE);
        $this->_cache->setString($this->_key1, 'bar', 'ing', Swift_KeyCache::MODE_WRITE);
        $this->assertSame('test', $this->_cache->getString($this->_key1, 'foo'));
        $this->assertSame('ing', $this->_cache->getString($this->_key1, 'bar'));
    }

    public function testByteStreamCanBeImported()
    {
        $os = new Swift_ByteStream_ArrayByteStream();
        $os->write('abcdef');

        $this->_cache->importFromByteStream($this->_key1, 'foo', $os, Swift_KeyCache::MODE_WRITE);
        $this->assertSame('abcdef', $this->_cache->getString($this->_key1, 'foo'));
    }

    public function testByteStreamCanBeAppended()
    {
        $os1 = new Swift_ByteStream_ArrayByteStream();
        $os1->write('abcdef');

        $os2 = new Swift_ByteStream_ArrayByteStream();
        $os2->write('xyzuvw');

        $this->_cache->importFromByteStream($this->_key1, 'foo', $os1, Swift_KeyCache::MODE_APPEND);
        $this->_cache->importFromByteStream($this->_key1, 'foo', $os2, Swift_KeyCache::MODE_APPEND);

        $this->assertSame('abcdefxyzuvw', $this->_cache->getString($this->_key1, 'foo'));
    }

    public function testByteStreamAndStringCanBeAppended()
    {
        $this->_cache->setString($this->_key1, 'foo', 'test', Swift_KeyCache::MODE_APPEND);

        $os = new Swift_ByteStream_ArrayByteStream();
        $os->write('abcdef');

        $this->_cache->importFromByteStream($this->_key1, 'foo', $os, Swift_KeyCache::MODE_APPEND);
        $this->assertSame('testabcdef', $this->_cache->getString($this->_key1, 'foo'));
    }

    public function testDataCanBeExportedToByteStream()
    {
        $this->_cache->setString($this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE);

        $is = new Swift_ByteStream_ArrayByteStream();

        $this->_cache->exportToByteStream($this->_key1, 'foo', $is);

        $string = '';
        while (false !== $bytes = $is->read(8192)) {
            $string .= $bytes;
        }

        $this->assertSame('test', $string);
    }

    public function testKeyCanBeCleared()
    {
        $this->_cache->setString($this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE);
        $this->assertTrue($this->_cache->hasKey($this->_key1, 'foo'));
        $this->_cache->clearKey($this->_key1, 'foo');
        $this->assertFalse($this->_cache->hasKey($this->_key1, 'foo'));
    }

    public function testNsKeyCanBeCleared()
    {
        $this->_cache->setString($this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE);
        $this->_cache->setString($this->_key1, 'bar', 'xyz', Swift_KeyCache::MODE_WRITE);
        $this->assertTrue($this->_cache->hasKey($this->_key1, 'foo'));
        $this->assertTrue($this->_cache->hasKey($this->_key1, 'bar'));
        $this->_cache->clearAll($this->_key1);
        $this->assertFalse($this->_cache->hasKey($this->_key1, 'foo'));
        $this->assertFalse($this->_cache->hasKey($this->_key1, 'bar'));
    }

    public function testKeyCacheInputStream()
    {
        $is = $this->_cache->getInputByteStream($this->_key1, 'foo');
        $is->write('abc');
        $is->write('xyz');
        $this->assertSame('abcxyz', $this->_cache->getString($this->_key1, 'foo'));
    }
}
