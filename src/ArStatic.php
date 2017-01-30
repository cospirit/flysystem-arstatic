<?php
namespace ArDev\Flysystem\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\NotSupportedException;
use League\Flysystem\Util;

class ArStatic implements AdapterInterface
{
    /**
     * @var string
     */
    protected $apiUrl;

    /**
     * @var string
     */
    protected $application;

    /**
     * @param string $apiUrl
     * @param string $application
     */
    public function __construct($apiUrl, $application)
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->application = $application;
    }

    /**
     * @param string $application
     * @return self 
     */
    public function setApplication($application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Write a new file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     * @return array|false false on failure file meta data on success
     */
    public function write($path, $contents, Config $config = null)
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'arconnect-static');
        file_put_contents($tmpFile, $contents);

        $file = new \CURLFile(
            $tmpFile,
            Util::guessMimeType($path, $tmpFile),
            basename($tmpFile)
        );

        $data = [
            'slug' => $path,
            'file' => $file,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl.'/'.$this->application);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        $fileInfo = curl_getinfo($ch);
        curl_close($ch);

        unlink($tmpFile);

        if ($fileInfo['http_code'] >= 200 && $fileInfo['http_code'] < 400) {
            $response = [
                'contents' => $contents,
                'type'     => $fileInfo['content_type'],
                'size'     => $fileInfo['size_download'],
                'path'     => $path,
            ];

            return $response;
        }

        return false;
    }

    /**
     * Write a new file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     * @return array|false false on failure file meta data on success
     *
     * @throws NotSupportedException
     */
    public function writeStream($path, $resource, Config $config)
    {
        throw new NotSupportedException();
    }

    /**
     * Update a file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     * @return array|false false on failure file meta data on success
     *
     * @throws NotSupportedException
     */
    public function update($path, $contents, Config $config)
    {
        throw new NotSupportedException();
    }

    /**
     * Update a file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     * @return array|false false on failure file meta data on success
     *
     * @throws NotSupportedException
     */
    public function updateStream($path, $resource, Config $config)
    {
        throw new NotSupportedException();
    }

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newpath
     * @return bool
     *
     * @throws NotSupportedException
     */
    public function rename($path, $newpath)
    {
        throw new NotSupportedException();
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newpath
     * @return bool
     *
     * @throws NotSupportedException
     */
    public function copy($path, $newpath)
    {
        throw new NotSupportedException();
    }

    /**
     * Delete a file.
     *
     * @param string $path
     * @return bool
     */
    public function delete($path)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl.'/'.$this->application.'/'.$path);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_exec($ch);
        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($response >= 200 && $response < 400) ? true : false;
    }

    /**
     * Delete a directory.
     *
     * @param string $dirname
     * @return bool
     *
     * @throws NotSupportedException
     */
    public function deleteDir($dirname)
    {
        throw new NotSupportedException();
    }

    /**
     * Create a directory.
     *
     * @param string $dirname directory name
     * @param Config $config
     * @return array|false
     *
     * @throws NotSupportedException
     */
    public function createDir($dirname, Config $config)
    {
        throw new NotSupportedException();
    }

    /**
     * Set the visibility for a file.
     *
     * @param string $path
     * @param string $visibility
     * @return array|false file meta data
     *
     * @throws NotSupportedException
     */
    public function setVisibility($path, $visibility)
    {
        throw new NotSupportedException();
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     * @return bool
     */
    public function has($path)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl.'/'.$this->application.'/'.$path);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_exec($ch);
        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($response >= 200 && $response < 400) ? true : false;
    }

    /**
     * Read a file.
     *
     * @param string $path
     * @return array|false
     */
    public function read($path)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl.'/'.$this->application.'/'.$path);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $contents = curl_exec($ch);
        curl_close($ch);

        if ($contents === false) {
            return false;
        }

        return [
            'contents' => $contents,
            'path'     => $path,
        ];
    }

    /**
     * Read a file as a stream.
     *
     * @param string $path
     * @return array|false
     */
    public function readStream($path)
    {
        throw new NotSupportedException();
    }

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     * @return array
     */
    public function listContents($directory = '', $recursive = false)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->apiUrl.'/'.$this->application.'/'.$directory.'/list');
        curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true) ;
        curl_setopt ($curl, CURLOPT_TIMEOUT, '12');

        $root = curl_exec($curl);
        $directories = json_decode($root);
        curl_close($curl);

        return $directories;
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     * @return array|false
     *
     * @throws NotSupportedException
     */
    public function getMetadata($path)
    {
        throw new NotSupportedException();
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     * @return array|false
     *
     * @throws NotSupportedException
     */
    public function getSize($path)
    {
        throw new NotSupportedException();
    }

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     * @return array|false
     *
     * @throws NotSupportedException
     */
    public function getMimetype($path)
    {
        throw new NotSupportedException();
    }

    /**
     * Get the timestamp of a file.
     *
     * @param string $path
     * @return array|false
     *
     * @throws NotSupportedException
     */
    public function getTimestamp($path)
    {
        throw new NotSupportedException();
    }

    /**
     * Get the visibility of a file.
     *
     * @param string $path
     * @return array|false
     *
     * @throws NotSupportedException
     */
    public function getVisibility($path)
    {
        throw new NotSupportedException();
    }

    /**
     * @param string $slug
     * @return bool|string
     */
    public function getAbsolutePath($slug = '')
    {
        $absolutePath = sprintf('%s/%s/', $this->apiUrl, $this->application);

        if (!empty($slug)) {
            if ($this->has($slug)) {
                return $absolutePath . $slug;
            }

            return false;
        }

        return $absolutePath;
    }
}
