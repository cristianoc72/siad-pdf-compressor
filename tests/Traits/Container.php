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

use cristianoc72\PdfCompressor\Container as BaseContainer;
use Ilovepdf\CompressTask;
use Ilovepdf\Exceptions\AuthException;
use Ilovepdf\Exceptions\DownloadException;
use Ilovepdf\Ilovepdf;
use Ilovepdf\MergeTask;

trait Container
{
    private BaseContainer $testContainer;

    public function getContainer(): BaseContainer
    {
        if (!isset($this->testContainer)) {
            $iLovePdfStub = $this->createStub(Ilovepdf::class);
            $taskCompressStub = $this->createStub(CompressTask::class);
            $taskMergeStub = $this->createStub(MergeTask::class);
            $iLovePdfStub->method('newTask')->willReturnMap([
                ['merge', $taskMergeStub],
                ['compress', $taskCompressStub]
            ]);
            $root = $this->getRoot();
            $this->testContainer = new BaseContainer($root->url());
            $this->testContainer->set('iLovePdf', $iLovePdfStub);
        }

        return $this->testContainer;
    }

    protected function getIlovePdfWithAuthException(): Ilovepdf
    {
        $taskMergeStub = $this->createStub(MergeTask::class);
        $taskMergeStub->method('execute')->willThrowException(new AuthException('Invalid credentials', null, 401, null));
        $taskCompressStub = $this->createStub(CompressTask::class);
        $iLovePdfStub = $this->createStub(Ilovepdf::class);
        $iLovePdfStub->method('newTask')->willReturnMap([
            ['merge', $taskMergeStub],
            ['compress', $taskCompressStub]
        ]);

        return $iLovePdfStub;
    }

    protected function getIlovePdfWithDownloadException(): Ilovepdf
    {
        $taskMergeStub = $this->createStub(MergeTask::class);
        $taskCompressStub = $this->createStub(CompressTask::class);
        $taskCompressStub->method('download')->willThrowException(new DownloadException('Download error', null, 320, null));
        $iLovePdfStub = $this->createStub(Ilovepdf::class);
        $iLovePdfStub->method('newTask')->willReturnMap([
            ['merge', $taskMergeStub],
            ['compress', $taskCompressStub]
        ]);

        return $iLovePdfStub;
    }

    protected function getIlovePdfWithException(): Ilovepdf
    {
        $taskMergeStub = $this->createStub(MergeTask::class);
        $taskCompressStub = $this->createStub(CompressTask::class);
        $taskCompressStub->method('execute')->willThrowException(new \Exception('Generic error'));
        $iLovePdfStub = $this->createStub(Ilovepdf::class);
        $iLovePdfStub->method('newTask')->willReturnMap([
            ['merge', $taskMergeStub],
            ['compress', $taskCompressStub]
        ]);

        return $iLovePdfStub;
    }
}
