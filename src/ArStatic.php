<?php

namespace CoSpirit\Flysystem\Adapter;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use League\MimeTypeDetection\MimeTypeDetector;

class ArStatic implements FilesystemAdapter
{
    public function __construct(
        protected string $apiUrl,
        protected string $application,
        private readonly MimeTypeDetector $mimeTypeDetector
    ) {
        $this->apiUrl = rtrim($apiUrl, '/');
    }

    public function getAbsolutePath(string $slug = ''): string|false
    {
        $absolutePath = sprintf('%s/%s/', $this->apiUrl, $this->application);
        if (!empty($slug)) {
            if ($this->fileExists($slug)) {
                return $absolutePath.$slug;
            }

            return false;
        }

        return $absolutePath;
    }

    public function setApplication(string $application): self
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Write a new file.
     */
    public function write(string $path, string $contents, Config $config): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'arconnect-static');
        file_put_contents($tmpFile, $contents);

        $file = new \CURLFile(
            $tmpFile,
            $this->mimeTypeDetector->detectMimeTypeFromFile($tmpFile) ?? $this->mimeTypeDetector->detectMimeTypeFromPath($path),
            basename($tmpFile)
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl.'/'.$this->application);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'slug' => $path,
            'file' => $file,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        $fileInfo = $this->getInfo($ch);
        curl_close($ch);

        unlink($tmpFile);

        if ($fileInfo['http_code'] < 200 || $fileInfo['http_code'] >= 400) {
            throw UnableToWriteFile::atLocation($path, "Unexpected response status code: \"{$fileInfo['http_code']}\"");
        }
    }

    /**
     * Write a new file using a stream.
     *
     * @param resource $contents
     *
     * @throws NotSupportedException
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        throw new NotSupportedException();
    }

    /**
     * Copy a file.
     *
     * @throws NotSupportedException
     */
    public function copy(string $source, string $destination, Config $config): void
    {
        throw new NotSupportedException();
    }

    /**
     * Delete a file.
     */
    public function delete(string $path): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl.'/'.$this->application.'/'.$path);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_exec($ch);
        $response = $this->getInfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response < 200 || $response >= 400) {
            throw UnableToDeleteFile::atLocation($path, "Unexpected response status code: \"$response\"");
        }
    }

    /**
     * Set the visibility for a file.
     *
     * @throws NotSupportedException
     */
    public function setVisibility(string $path, string $visibility): void
    {
        throw new NotSupportedException();
    }

    /**
     * Read a file.
     */
    public function read(string $path): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl.'/'.$this->application.'/'.$path);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $contents = curl_exec($ch);
        $responseStatus = $this->getInfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseStatus < 200 || $responseStatus >= 400) {
            throw UnableToReadFile::fromLocation($path, "Unexpected response status code: \"$responseStatus\"");
        }

        return $contents;
    }

    /**
     * Read a file as a stream.
     */
    public function readStream(string $path)
    {
        throw new NotSupportedException();
    }

    /**
     * List contents of a directory.
     */
    public function listContents(string $path, bool $deep): iterable
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->apiUrl.'/'.$this->application.'/'.$path.'/list');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, '12');

        $root = curl_exec($curl);
        $directories = json_decode($root);
        curl_close($curl);

        return $directories;
    }

    public function fileExists(string $path): bool
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl.'/'.$this->application.'/'.$path);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_exec($ch);
        $response = $this->getInfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $response >= 200 && $response < 400;
    }

    public function directoryExists(string $path): bool
    {
        return false;
    }

    public function deleteDirectory(string $path): void
    {
        throw new NotSupportedException();
    }

    public function createDirectory(string $path, Config $config): void
    {
        throw new NotSupportedException();
    }

    public function visibility(string $path): FileAttributes
    {
        throw new NotSupportedException();
    }

    public function mimeType(string $path): FileAttributes
    {
        throw new NotSupportedException();
    }

    public function lastModified(string $path): FileAttributes
    {
        throw new NotSupportedException();
    }

    public function fileSize(string $path): FileAttributes
    {
        throw new NotSupportedException();
    }

    public function move(string $source, string $destination, Config $config): void
    {
        throw new NotSupportedException();
    }

    /**
     * @return mixed If opt is given, returns its value as a string.
     *               Otherwise, returns an associative array.
     */
    private function getInfo(\CurlHandle $ch, int $opt = null): mixed
    {
        if (curl_errno($ch)) {
            return false;
        }

        $response = $opt ? curl_getinfo($ch, $opt) : curl_getinfo($ch);

        // Wait max 5 secondes for streamed response
        $counter = 0;
        while (!$response && $counter < 5) {
            sleep(1);
            ++$counter;
            $response = curl_getinfo($ch, $opt);
        }

        return $response;
    }
}
