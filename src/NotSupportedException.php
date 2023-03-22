<?php

declare(strict_types=1);

namespace CoSpirit\Flysystem\Adapter;

use League\Flysystem\FilesystemException;
use RuntimeException;

class NotSupportedException extends RuntimeException implements FilesystemException
{
}