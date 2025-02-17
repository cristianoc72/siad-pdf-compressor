<?php declare(strict_types=1);
/**
 * Copyright (c) 2021 - 2025 Cristiano Cinotti
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
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

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
DOCS_DIR=" . vfsStream::url('root/docs/2024') . "
LOG_FILE=" . vfsStream::url('root') . "/pdf-compressor.log
DISABLE_PREINVOICE=true
"
        );
    }

    protected function populateFilesystem(): void
    {
        $this->createDotEnv();
        $docsDir = vfsStream::newDirectory('docs')->at($this->getRoot());
        $dir = vfsStream::newDirectory('2024')->at($docsDir);

        for ($i = 0; $i < 5; $i++) {
            $doc = vfsStream::newFile("PraticaCollaudata_$i.PDF")
                ->at($dir)->withContent(LargeFileContent::withKilobytes(300))
            ;
            $conf = vfsStream::newFile("ConformitaFirmata_$i.PDF")
                ->at($dir)->withContent(LargeFileContent::withKilobytes(100))
            ;
        }
    }

    protected function populateForPreInvoice(): void
    {
        $envFile = vfsStream::newFile('.env')->at($this->getRoot())->setContent(
            "
PUBLIC_KEY=public_key
PRIVATE_KEY=private_key
DOCS_DIR=" . vfsStream::url('root/docs/2024') . "
LOG_FILE=" . vfsStream::url('root') . "/pdf-compressor.log
DISABLE_PREINVOICE=false
"
        );

        $docsDir = vfsStream::newDirectory('docs')->at($this->getRoot());
        $dir = vfsStream::newDirectory('2024')->at($docsDir);

        for ($i = 0; $i < 5; $i++) {
            $tempDir = vfsStream::newDirectory("E_$i")->at($dir);
            $doc = vfsStream::newFile("PraticaCollaudata_$i.PDF")
                ->at($tempDir)->withContent(LargeFileContent::withKilobytes(300))
            ;
        }
    }

    protected function populateWithOneNotReadableFile(): void
    {
        $dir = vfsStream::newDirectory('docs')->at($this->getRoot());
        $docsDir = vfsStream::newDirectory('2024')->at($dir);
        $this->createDotEnv();

        for ($i = 0; $i < 4; $i++) {
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
        $dir = vfsStream::newDirectory('docs')->at($this->getRoot());
        $docsDir = vfsStream::newDirectory('2024')->at($dir);
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
        $dir = vfsStream::newDirectory('docs')->at($this->getRoot());
        $docsDir = vfsStream::newDirectory('2024')->at($dir);
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
        $dir = vfsStream::newDirectory('2024')->at($docsDir);
        $this->createDotEnv();

        for ($i = 0; $i < 5; $i++) {
            vfsStream::newFile("Original_pratica_collaudata_$i.PDF")
                ->at($dir)->withContent(LargeFileContent::withKilobytes(300));
            ;
            vfsStream::newFile("PraticaCollaudata_$i.PDF")
                ->at($dir)->setContent("Compressed PraticaCollaudata_$i.PDF")
            ;
        }
    }

    protected function populateNotWriteableFilesToRestore(): void
    {
        $dir = vfsStream::newDirectory('docs')->at($this->getRoot());
        $docsDir = vfsStream::newDirectory('2024')->at($dir);
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

    protected function populateWithPreviousYear(): void
    {
        $this->createDotEnv();
        $docsDir = vfsStream::newDirectory('docs')->at($this->getRoot());
        $dir1 = vfsStream::newDirectory('2024')->at($docsDir);
        $dir2 = vfsStream::newDirectory('2023')->at($docsDir);

        for ($i = 0; $i < 5; $i++) {
            $doc = vfsStream::newFile("PraticaCollaudata_$i.PDF")
                ->at($dir1)->withContent(LargeFileContent::withKilobytes(300))
            ;
        }

        for ($i = 5; $i < 10; $i++) {
            $doc = vfsStream::newFile("PraticaCollaudata_$i.PDF")
                ->at($dir2)->withContent(LargeFileContent::withKilobytes(300))
            ;
        }
    }
}
