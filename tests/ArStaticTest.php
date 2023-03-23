<?php

namespace Test\CoSpirit\Flysystem\Adapter;

use CoSpirit\Flysystem\Adapter\ArStatic;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use PHPUnit\Framework\TestCase;

class ArStaticTest extends TestCase
{
    protected string $apiUrl      = "http://localhost:8989/index.php";
    protected string $application = "application-test";
    protected string $slug        = "cospirit-connect.png";
    protected string $slugWrong   = "none.png";
    private readonly string $sourceFile;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->sourceFile = __DIR__.'/assets/cospirit-connect.png';
    }

    protected function initAdapter(): ArStatic
    {
        return new ArStatic($this->apiUrl, $this->application, new FinfoMimeTypeDetector());
    }

    public function testWrite(): void
    {
        $filesystem = new Filesystem($this->initAdapter());
        $filesystem->write($this->slug, file_get_contents($this->sourceFile));
        $this->assertStringEqualsFile($this->sourceFile, $filesystem->read($this->slug));
    }

    /**
     * @depends testWrite
     */
    public function testRead(): void
    {
        $filesystem = new Filesystem($this->initAdapter());
        $this->assertStringEqualsFile($this->sourceFile, $filesystem->read($this->slug));

        $this->expectException(UnableToReadFile::class);
        $filesystem->read($this->slugWrong);
    }

    /**
     * @depends testRead
     */
    public function testFileExists(): void
    {
        $filesystem = new Filesystem($this->initAdapter());
        $this->assertEquals(true, $filesystem->has($this->slug));
        $this->assertEquals(false, $filesystem->has($this->slugWrong));
    }

    /**
     * @depends testFileExists
     */
    public function testDelete(): void
    {
        $filesystem = new Filesystem($this->initAdapter());
        $filesystem->delete($this->slug);
        $this->assertFalse($filesystem->has($this->slug));

        $this->expectException(UnableToDeleteFile::class);
        $filesystem->delete($this->slugWrong);
    }
}
