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

use cristianoc72\PdfCompressor\Container;
use Ilovepdf\CompressTask;
use Ilovepdf\Exceptions\AuthException;
use Ilovepdf\Exceptions\DownloadException;
use org\bovigo\vfs\content\LargeFileContent;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    private vfsStreamDirectory $root;
    private Container $testContainer;

    public function setUp(): void
    {
        $this->root = vfsStream::setup();
        $this->populateFilesystem();
    }

    public function getContainer(): Container
    {
        if (!isset($this->testContainer)) {
            $iLovePdfMock = $this->getMockBuilder(CompressTask::class)
                ->disableOriginalConstructor()
                ->getMock();
            $root = $this->getRoot();
            $this->testContainer = new Container($root->url());
            $this->testContainer->set('iLovePdf', $iLovePdfMock);
        }

        return $this->testContainer;
    }

    public function getRoot(): vfsStreamDirectory
    {
        return $this->root;
    }

    protected function getIlovePdfWithAuthException(): CompressTask
    {
        $iLovePdfMock = $this->getMockBuilder(CompressTask::class)
            ->disableOriginalConstructor()
            ->getMock();
        $iLovePdfMock->method('execute')
            ->willThrowException(new AuthException('Invalid credentials', 401, null, ''));

        return $iLovePdfMock;
    }

    protected function getIlovePdfWithDownloadException(): CompressTask
    {
        $iLovePdfMock = $this->getMockBuilder(CompressTask::class)
            ->disableOriginalConstructor()
            ->getMock();
        $iLovePdfMock->method('download')
            ->willThrowException(new DownloadException('Download error', 320, null, ''));

        return $iLovePdfMock;
    }

    protected function getIlovePdfWithException(): CompressTask
    {
        $iLovePdfMock = $this->getMockBuilder(CompressTask::class)
            ->disableOriginalConstructor()
            ->getMock();
        $iLovePdfMock->method('execute')
            ->willThrowException(new \Exception('Generic error'));

        return $iLovePdfMock;
    }

    private function populateFilesystem(): void
    {
        $docsDir = vfsStream::newDirectory('docs')->at($this->root);
        $dotEnv = vfsStream::newFile('.env')->at($this->root)->setContent(
            "
PUBLIC_KEY=public_key
PRIVATE_KEY=private_key
DOCS_DIR={$docsDir->url()}
"
        );
        for ($i = 0; $i < 5; $i++) {
            $doc = vfsStream::newFile("PraticaCollaudata_$i.PDF")
                ->at($docsDir)->withContent(LargeFileContent::withKilobytes(300))
            ;
        }
    }
}
