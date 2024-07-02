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

use cristianoc72\PdfCompressor\Container as BaseContainer;
use Ilovepdf\CompressTask;
use Ilovepdf\Exceptions\AuthException;
use Ilovepdf\Exceptions\DownloadException;
use Ilovepdf\Ilovepdf;

trait Container
{
    private BaseContainer $testContainer;

    public function getContainer(): BaseContainer
    {
        if (!isset($this->testContainer)) {
            $iLovePdfMock = $this->getMockBuilder(Ilovepdf::class)
                ->disableOriginalConstructor()
                ->getMock();
            $taskMock = $this->getMockBuilder(CompressTask::class)->disableOriginalConstructor()->getMock();
            $iLovePdfMock->method('newTask')->willReturn($taskMock);
            $root = $this->getRoot();
            $this->testContainer = new BaseContainer($root->url());
            $this->testContainer->set('iLovePdf', $iLovePdfMock);
        }

        return $this->testContainer;
    }

    protected function getIlovePdfWithAuthException(): Ilovepdf
    {
        $task = $this->getMockBuilder(CompressTask::class)
            ->disableOriginalConstructor()
            ->getMock();
        $task->method('execute')
            ->willThrowException(new AuthException('Invalid credentials', 401, null, null));
        $iLovePdfMock = $this->getMockBuilder(Ilovepdf::class)
            ->disableOriginalConstructor()
            ->getMock();
        $iLovePdfMock->method('newTask')->willReturn($task);

        return $iLovePdfMock;
    }

    protected function getIlovePdfWithDownloadException(): Ilovepdf
    {
        $task = $this->getMockBuilder(CompressTask::class)
            ->disableOriginalConstructor()
            ->getMock();
        $task->method('download')
            ->willThrowException(new DownloadException('Download error', 320, null, null));
        $iLovePdfMock = $this->getMockBuilder(Ilovepdf::class)
            ->disableOriginalConstructor()
            ->getMock();
        $iLovePdfMock->method('newTask')->willReturn($task);

        return $iLovePdfMock;
    }

    protected function getIlovePdfWithException(): Ilovepdf
    {
        $task = $this->getMockBuilder(CompressTask::class)
            ->disableOriginalConstructor()
            ->getMock();
        $task->method('execute')
            ->willThrowException(new \Exception('Generic error'));
        $iLovePdfMock = $this->getMockBuilder(Ilovepdf::class)
            ->disableOriginalConstructor()
            ->getMock();
        $iLovePdfMock->method('newTask')->willReturn($task);

        return $iLovePdfMock;
    }
}
