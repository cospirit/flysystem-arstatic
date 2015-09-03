<?php

namespace Test\ArDev\Flysystem\Adapter;

use ArDev\Flysystem\Adapter\ArStatic;
use League\Flysystem\Filesystem;

class ArStaticTest extends \PHPUnit_Framework_TestCase
{
    protected $apiUrl      = "http://localhost:8989/index.php";
    protected $application = "application-test";
    protected $slug        = "ar-connect.png";
    protected $slugWrong   = "none.png";

    protected function initAdapter()
    {
        return new ArStatic($this->apiUrl, $this->application);
    }

    public function testWrite()
    {
        $file = __DIR__.'/assets/ar-connect.png';
        $filesystem = new Filesystem($this->initAdapter());
        $this->assertEquals(true, $filesystem->write($this->slug, file_get_contents($file)));
    }

    /**
     * @depends testWrite
     * @expectedException \League\Flysystem\FileNotFoundException
     */
    public function testRead()
    {
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
     * @expectedException \League\Flysystem\FileNotFoundException
     */
    public function testDelete()
    {
        $filesystem = new Filesystem($this->initAdapter());
        $this->assertEquals(true, $filesystem->delete($this->slug));
        $this->assertEquals(false, $filesystem->delete($this->slugWrong));
    }
}
