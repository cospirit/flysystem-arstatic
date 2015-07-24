# flysystem-ardev-static

This is an HTTP client based on cURL to consume STATIC API

![](http://imagizer.imageshack.com/img912/632/i5am6S.jpg)

## Develop

### Requirements

* [Composer]( https://github.com/composer/composer)

Clone the project

    $ git@github.com:ArDeveloppement/flysystem-ardev-static.git
    $ cd flysystem-ardev-static
    
Initialize project

    ⇒ composer install
    
### Usage

    use ArDev\Flysystem\Adapter\ArStatic;
    use League\Flysystem\Filesystem;
    
    $adapter = new ArStatic($apiUrl);
    $filesystem = new Filesystem($adapter);

#### Implemented methods

Write

    $filesystem->write($slug, file_get_contents($tmpName));
    
Read

    $filesystem->read($slug);
    
Has

    $filesystem->has($slug);
    
Delete

    $filesystem->delete($slug);
    
### Tests

    make test-suite
