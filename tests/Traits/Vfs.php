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
use org\bovigo\vfs\visitor\vfsStreamPrintVisitor;
use Symfony\Component\Yaml\Yaml;

trait Vfs
{
    public private(set) vfsStreamDirectory $root {
        get => $this->root ?? $this->root = vfsStream::setup();
    }

    private array $configContent = [
        'public_key' => 'public_key',
        'private_key' => 'private_key',
        'docs_dir' => 'vfs://root/docs/2024',
        'log_file' => 'vfs://root/pdf-compressor.log',
        'disable_preinvoice' => true
    ];

    // Several populator methods

    public function createConfigFile(): vfsStreamFile
    {
        return vfsStream::newFile('siad-pdf-compressor.yaml')
            ->at($this->root)
            ->setContent(Yaml::dump($this->configContent));
    }

    protected function populateFilesystem(): void
    {
        $this->createConfigFile();
        $docsDir = vfsStream::newDirectory('docs')->at($this->root);
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
        $content = $this->configContent;
        $content['disable_preinvoice'] = false;

        vfsStream::newFile('siad-pdf-compressor.yaml')
            ->at($this->root)
            ->setContent(Yaml::dump($content));
        $docsDir = vfsStream::newDirectory('docs')->at($this->root);
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
        $dir = vfsStream::newDirectory('docs')->at($this->root);
        $docsDir = vfsStream::newDirectory('2024')->at($dir);
        $this->createConfigFile();

        for ($i = 0; $i < 4; $i++) {
            $doc = vfsStream::newFile("PraticaCollaudata_$i.PDF")
                ->at($docsDir)->withContent(LargeFileContent::withKilobytes(500))
            ;
        }

        vfsStream::newFile("PraticaCollaudata_5.PDF")
            ->at($docsDir)->withContent(LargeFileContent::withKilobytes(500))->chmod(000)
        ;
    }

    protected function populateWithUnreadableConfigFile(): void
    {
        $dir = vfsStream::newDirectory('docs')->at($this->root);
        $docsDir = vfsStream::newDirectory('2024')->at($dir);
        $dotEnv = $this->createConfigFile();
        $dotEnv->chmod(000);

        for ($i = 0; $i < 5; $i++) {
            $doc = vfsStream::newFile("PraticaCollaudata_$i.PDF")
                ->at($docsDir)->withContent(LargeFileContent::withKilobytes(500))
            ;
        }
    }

    protected function populateWithNotWriteableConfigFile(): void
    {
        $dir = vfsStream::newDirectory('docs')->at($this->root);
        $docsDir = vfsStream::newDirectory('2024')->at($dir);
        $dotEnv = $this->createConfigFile();
        $dotEnv->chmod(0400);

        for ($i = 0; $i < 5; $i++) {
            $doc = vfsStream::newFile("PraticaCollaudata_$i.PDF")
                ->at($docsDir)->withContent(LargeFileContent::withKilobytes(500))
            ;
        }
    }

    protected function populateFilesToRestore(): void
    {
        $docsDir = vfsStream::newDirectory('docs')->at($this->root);
        $dir = vfsStream::newDirectory('2024')->at($docsDir);
        $this->createConfigFile();

        for ($i = 0; $i < 5; $i++) {
            vfsStream::newFile("Original_pratica_collaudata_$i.PDF")
                ->at($dir)->withContent(LargeFileContent::withKilobytes(300));
            ;
            vfsStream::newFile("PraticaCollaudata_$i.PDF")
                ->at($dir)->setContent("Compressed PraticaCollaudata_$i.PDF")
            ;
        }
    }

    protected function populateFilesToRestoreInDifferentDirs(): void
    {
        $docsDir = vfsStream::newDirectory('docs')->at($this->root);
        $dir = vfsStream::newDirectory('2024')->at($docsDir);
        $givenDir = vfsStream::newDirectory('E_given')->at($dir);
        $notGivenDir = vfsStream::newDirectory('E_notgiven')->at($dir);
        $this->createConfigFile();

        for ($i = 0; $i < 3; $i++) {
            vfsStream::newFile("Original_pratica_collaudata_$i.PDF")
                ->at($givenDir)->withContent(LargeFileContent::withKilobytes(300));
            ;
            vfsStream::newFile("PraticaCollaudata_$i.PDF")
                ->at($givenDir)->setContent("Compressed PraticaCollaudata_$i.PDF")
            ;
        }

        for ($i = 3; $i < 6; $i++) {
            vfsStream::newFile("Original_pratica_collaudata_$i.PDF")
                ->at($notGivenDir)->withContent(LargeFileContent::withKilobytes(300));
            ;
            vfsStream::newFile("PraticaCollaudata_$i.PDF")
                ->at($notGivenDir)->setContent("Compressed PraticaCollaudata_$i.PDF")
            ;
        }
    }

    protected function populateNotWriteableFilesToRestore(): void
    {
        $dir = vfsStream::newDirectory('docs')->at($this->root);
        $docsDir = vfsStream::newDirectory('2024')->at($dir);
        $this->createConfigFile();

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
        $this->createConfigFile();
        $docsDir = vfsStream::newDirectory('docs')->at($this->root);
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

    protected function populateWithExcludes(): void
    {
        $content = [
            'public_key' => 'public_key',
            'private_key' => 'private_key',
            'docs_dir' => 'vfs://root/docs/2024',
            'log_file' => 'vfs://root/pdf-compressor.log',
            'disable_preinvoice' => true,
            'excludes' => ['excluded']
        ];
        vfsStream::newFile('siad-pdf-compressor.yaml')
            ->at($this->root)
            ->setContent(Yaml::dump($content));
        
        $docsDir = vfsStream::newDirectory('docs')->at($this->root);
        $dir1 = vfsStream::newDirectory('2024')->at($docsDir);
        $dir2 = vfsStream::newDirectory('excluded')->at($dir1);

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
