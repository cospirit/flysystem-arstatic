<?php

namespace Test\CoSpirit\Flysystem\Adapter;

use CoSpirit\Flysystem\Adapter\ArStatic;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;

class ArStaticTest extends TestCase
{
    protected $apiUrl      = "http://localhost:8989/index.php";
    protected $application = "application-test";
    protected $slug        = "cospirit-connect.png";
    protected $slugWrong   = "none.png";

    protected function initAdapter()
    {
        return new ArStatic($this->apiUrl, $this->application);
    }

    public function testWrite()
    {
        $file = __DIR__.'/assets/cospirit-connect.png';
        $filesystem = new Filesystem($this->initAdapter());
        $this->assertEquals(true, $filesystem->write($this->slug, file_get_contents($file)));
    }

    /**
     * @depends testWrite
     */
    public function testRead()
    {
        $this->expectException(FileNotFoundException::class);

        $filesystem = new Filesystem($this->initAdapter());
        $this->assertTrue(is_string($filesystem->read($this->slug)));
        $this->assertEquals(false, $filesystem->read($this->slugWrong));
    }

    /**
     * @depends testRead
     */
    public function testHas()
    {
        $filesystem = new Filesystem($this->initAdapter());
        $this->assertEquals(true, $filesystem->has($this->slug));
        $this->assertEquals(false, $filesystem->has($this->slugWrong));
    }

    /**
     * @depends testHas
     */
    public function testDelete()
    {
        $this->expectException(FileNotFoundException::class);

        $filesystem = new Filesystem($this->initAdapter());
        $this->assertEquals(true, $filesystem->delete($this->slug));
        $this->assertEquals(false, $filesystem->delete($this->slugWrong));
    }
}
