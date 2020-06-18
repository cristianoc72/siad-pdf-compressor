<?php declare(strict_types=1);
/**
 * Copyright (c) 2020 Cristiano Cinotti
 *
 * This file is part of siad-pdf-compressor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license Apache-2.0
 */

namespace cristianoc72\PdfCompressor\Tests;


use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    private vfsStreamDirectory $root;

    public function getRoot(): vfsStreamDirectory
    {
        if (!isset($this->root)) {
            $this->root = vfsStream::setup();
        }

        return $this->root;
    }
}
