# flysystem-arstatic

This is an HTTP client based on cURL to consume STATIC API

![](http://imagizer.imageshack.com/img912/632/i5am6S.jpg)

## Develop

### Requirements

* [Composer]( https://github.com/composer/composer)

Clone the project

    $ git@github.com:ArDeveloppement/flysystem-arstatic.git
    $ cd flysystem-arstatic
    
Initialize project

    $ composer install
    
### Usage

```php
use ArDev\Flysystem\Adapter\ArStatic;
use League\Flysystem\Filesystem;

$adapter = new ArStatic($apiUrl);
$filesystem = new Filesystem($adapter);
```

#### Implemented methods

Write
```php
$filesystem->write($slug, file_get_contents($tmpName));
```
    
Read
```php
$filesystem->read($slug);
```
    
Has
```php
$filesystem->has($slug);
```
    
Delete
```php
$filesystem->delete($slug);
```
    
### Tests

    $ make test-suite
