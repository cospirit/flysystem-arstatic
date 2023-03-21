![CoSpirit Connect](doc/logo.jpg)

# flysystem-arstatic [![CircleCI](https://circleci.com/gh/cospirit/flysystem-arstatic.svg?style=shield&circle-token=83d86dff77250ed8812fe50f0df7ad7085e14261)](https://circleci.com/gh/cospirit/flysystem-arstatic)

This is an HTTP client based on cURL to consume Static API

## Development

### Requirements

Install Docker as described in the [_Docker_](https://app.gitbook.com/@cospirit-connect/s/guide-de-demarrage/installation-des-projets/prerequis/docker) section of the Start Guide.

### Installation

Check the [Start guide](https://app.gitbook.com/@cospirit-connect/s/guide-de-demarrage/) of the documentation for base initialization.

#### Initialize project

```bash
    make development@install
```

### Usage (with Docker)

Install the application :
```bash
    make development@install
```

Restart the docker compose service :
```bash
    make development@restart
```

Remove and clean docker containers :
```bash
    make development@down
```

## Tests

```bash
    make test@phpunit
```

### Usage

```php
use CoSpirit\Flysystem\Adapter\ArStatic;
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
