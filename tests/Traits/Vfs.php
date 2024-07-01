<?php declare(strict_types=1);
/**
 * Copyright (c) 2020 - 2024 Cristiano Cinotti
 *
 * This file is part of siad-pdf-compressor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license Apache-2.0
 */

namespace cristianoc72\PdfCompressor\Tests\Traits;

use org\bovigo\vfs\content\LargeFileContent;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;

trait Vfs
{
    private vfsStreamDirectory $root;

    /**
     * Set up and return the virtual filesystem
     */
    public function getRoot(): vfsStreamDirectory
    {
        return $this->root ?? $this->root = vfsStream::setup();
    }

    // Several populator methods

    public function createDotEnv(): vfsStreamFile
    {
        return vfsStream::newFile('.env')->at($this->getRoot())->setContent(
            "
PUBLIC_KEY=public_key
PRIVATE_KEY=private_key
DOCS_DIR=" . vfsStream::url('root/docs') . "
LOG_FILE=" . vfsStream::url('root') . "/pdf-compressor.log 
"
        );
    }

    protected function populateFilesystem(): void
    {
        $this->createDotEnv();
        $docsDir = vfsStream::newDirectory('docs')->at($this->getRoot());

        for ($i = 0; $i < 5; $i++) {
            $doc = vfsStream::newFile("PraticaCollaudata_$i.PDF")
                ->at($docsDir)->withContent(LargeFileContent::withKilobytes(300))
            ;
        }
    }

    protected function populateWithOneNotReadableFile(): void
    {
        $docsDir = vfsStream::newDirectory('docs')->at($this->getRoot());
        $this->createDotEnv();

        for ($i = 0; $i < 5; $i++) {
            $doc = vfsStream::newFile("PraticaCollaudata_$i.PDF")
                ->at($docsDir)->withContent(LargeFileContent::withKilobytes(500))
            ;
        }

        vfsStream::newFile("PraticaCollaudata_5.PDF")
            ->at($docsDir)->withContent(LargeFileContent::withKilobytes(500))->chmod(000)
        ;
    }

    protected function populateWithUnreadableEnvFile(): void
    {
        $docsDir = vfsStream::newDirectory('docs')->at($this->getRoot());
        $dotEnv = $this->createDotEnv();
        $dotEnv->chmod(000);

        for ($i = 0; $i < 5; $i++) {
            $doc = vfsStream::newFile("PraticaCollaudata_$i.PDF")
                ->at($docsDir)->withContent(LargeFileContent::withKilobytes(500))
            ;
        }
    }

    protected function populateWithNotWriteableEnvFile(): void
    {
        $docsDir = vfsStream::newDirectory('docs')->at($this->getRoot());
        $dotEnv = $this->createDotEnv();
        $dotEnv->chmod(0400);

        for ($i = 0; $i < 5; $i++) {
            $doc = vfsStream::newFile("PraticaCollaudata_$i.PDF")
                ->at($docsDir)->withContent(LargeFileContent::withKilobytes(500))
            ;
        }
    }

    protected function populateFilesToRestore(): void
    {
        $docsDir = vfsStream::newDirectory('docs')->at($this->getRoot());
        $this->createDotEnv();

        for ($i = 0; $i < 5; $i++) {
            vfsStream::newFile("Original_pratica_collaudata_$i.PDF")
                ->at($docsDir)->withContent(LargeFileContent::withKilobytes(300));
            ;
            vfsStream::newFile("PraticaCollaudata_$i.PDF")
                ->at($docsDir)->setContent("Compressed PraticaCollaudata_$i.PDF")
            ;
        }
    }

    protected function populateNotWriteableFilesToRestore(): void
    {
        $docsDir = vfsStream::newDirectory('docs')->at($this->getRoot());
        $this->createDotEnv();

        for ($i = 0; $i < 5; $i++) {
            vfsStream::newFile("Original_pratica_collaudata_$i.PDF")
                ->at($docsDir)
                ->withContent(LargeFileContent::withKilobytes(300))
            ;

            vfsStream::newFile("PraticaCollaudata_$i.PDF")
                ->at($docsDir)
                ->setContent("Compressed PraticaCollaudata_$i.PDF")
            ;
        }

        $docsDir->chmod(0400);
    }
}
