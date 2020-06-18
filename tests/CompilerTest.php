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


use cristianoc72\PdfCompressor\Compiler;

class CompilerTest extends TestCase
{
    protected string $pharName;

    public function setUp(): void
    {
        $this->pharName = sys_get_temp_dir() . '/compressor.phar';

        $compiler = new Compiler();
        $compiler->compile($this->pharName);
    }
    public function testCompile(): void
    {
        $this->assertFileExists($this->pharName);

        //Test something else
    }

}