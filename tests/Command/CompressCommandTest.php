<?php declare(strict_types=1);
/*
 * Copyright (c) Cristiano Cinotti 2021.
 *
 * This file is part of siad-pdf-compressor package, release under the APACHE-2 license.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cristianoc72\PdfCompressor\Tests\Command;

use cristianoc72\PdfCompressor\Tests\TestCase;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Dotenv\Exception\PathException;

class CompressCommandTest extends TestCase
{
    public function testCompress(): void
    {
        $this->populateFilesystem();
        $container = $this->getContainer();

        $app = $container->get('app');
        $command = $app->find('compress');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--log-file' => vfsStream::url('root/pdf-compressor.log')
        ]);

        // test output
        $expectedOutput = "
Compression successfully executed!

Please, see the log file for further information.

Your log file path is: vfs://root/pdf-compressor.log
";
        $output = $commandTester->getDisplay(true);
        $this->assertStringContainsString($expectedOutput, $output);
        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertFileExists("{$this->getRoot()->url()}/pdf-compressor.log");

        $logContent = file_get_contents("{$this->getRoot()->url()}/pdf-compressor.log");

        for ($i = 0; $i < 5; $i++) {
            $this->assertFileExists("{$this->getRoot()->url()}/docs/Original_PraticaCollaudata_$i.PDF");
            $this->assertStringContainsString(
                "INFO: Backup `vfs://root/docs/PraticaCollaudata_$i.PDF` into `vfs://root/docs/Original_PraticaCollaudata_$i.PDF`.",
                $logContent
            );
            $this->assertStringContainsString("INFO: `vfs://root/docs/PraticaCollaudata_$i.PDF` compressed.", $logContent);
        }
    }

    public function testCompressWithFileErrors(): void
    {
        $this->populateWithOneNotReadableFile();

        $container = $this->getContainer();

        $app = $container->get('app');
        $command = $app->find('compress');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());

        // test output
        $expectedOutput = "
Compression executed with errors!

Please, see the log file or the displayed messages for further information.

Your log file path is: vfs://root/pdf-compressor.log
";
        $output = $commandTester->getDisplay(true);
        $this->assertStringContainsString($expectedOutput, $output);

        $this->assertFileExists("{$this->getRoot()->url()}/pdf-compressor.log");

        $logContent = file_get_contents("{$this->getRoot()->url()}/pdf-compressor.log");

        for ($i = 0; $i < 5; $i++) {
            $this->assertFileExists("{$this->getRoot()->url()}/docs/Original_PraticaCollaudata_$i.PDF");
            $this->assertFileExists("{$this->getRoot()->url()}/docs/PraticaCollaudata_$i.PDF");

            $this->assertStringContainsString("INFO: `vfs://root/docs/PraticaCollaudata_$i.PDF` compressed.", $logContent);
        }
        $this->assertStringContainsString(
            "ERROR: phootwork\\file\\exception\\FileException: Failed to copy vfs://root/docs/PraticaCollaudata_5.PDF to vfs://root/docs/Original_PraticaCollaudata_5.PDF",
            $logContent
        );
        $this->assertFileDoesNotExist("vfs://root/docs/Original_PraticaCollaudata_5.PDF");
        $this->assertFileExists("vfs://root/docs/PraticaCollaudata_5.PDF");
    }

    public function testCompressWithAuthError(): void
    {
        $this->populateFilesystem();
        $container = $this->getContainer();
        $container->set('iLovePdf', $this->getIlovePdfWithAuthException());

        $app = $container->get('app');
        $command = $app->find('compress');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());

        // test output
        $expectedOutput = "
Compression executed with errors!

Please, see the log file or the displayed messages for further information.

Your log file path is: vfs://root/pdf-compressor.log
";
        $output = $commandTester->getDisplay(true);
        $this->assertStringContainsString($expectedOutput, $output);

        $this->assertFileExists("{$this->getRoot()->url()}/pdf-compressor.log");

        $logContent = file_get_contents("{$this->getRoot()->url()}/pdf-compressor.log");

        for ($i = 0; $i < 5; $i++) {
            $this->assertFileDoesNotExist("{$this->getRoot()->url()}/docs/Original_PraticaCollaudata_$i.PDF");
            $this->assertFileExists("{$this->getRoot()->url()}/docs/PraticaCollaudata_$i.PDF");
            $this->assertStringContainsString(
                "INFO: Remove backup file `vfs://root/docs/Original_PraticaCollaudata_$i.PDF",
                $logContent
            );
        }
        $this->assertStringContainsString(
            "ERROR: Ilovepdf\Exceptions\AuthException: Invalid credentials",
            $logContent
        );
    }

    public function testCompressWithDownloadError(): void
    {
        $this->populateFilesystem();
        $container = $this->getContainer();
        $container->set('iLovePdf', $this->getIlovePdfWithDownloadException());

        $app = $container->get('app');
        $command = $app->find('compress');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());

        // test output
        $expectedOutput = "
Compression executed with errors!

Please, see the log file or the displayed messages for further information.

Your log file path is: vfs://root/pdf-compressor.log
";
        $output = $commandTester->getDisplay(true);
        $this->assertStringContainsString($expectedOutput, $output);

        $this->assertFileExists("{$this->getRoot()->url()}/pdf-compressor.log");

        $logContent = file_get_contents("{$this->getRoot()->url()}/pdf-compressor.log");

        for ($i = 0; $i < 5; $i++) {
            $this->assertFileDoesNotExist("{$this->getRoot()->url()}/docs/Original_PraticaCollaudata_$i.PDF");
            $this->assertFileExists("{$this->getRoot()->url()}/docs/PraticaCollaudata_$i.PDF");
            $this->assertStringContainsString(
                "INFO: Remove backup file `vfs://root/docs/Original_PraticaCollaudata_$i.PDF",
                $logContent
            );
        }
        $this->assertStringContainsString(
            "ERROR: Ilovepdf\Exceptions\DownloadException: Download error",
            $logContent
        );
    }

    public function testCompressWithGenericError(): void
    {
        $this->populateFilesystem();
        $container = $this->getContainer();
        $container->set('iLovePdf', $this->getIlovePdfWithException());

        $app = $container->get('app');
        $command = $app->find('compress');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());

        // test output
        $expectedOutput = "
Compression executed with errors!

Please, see the log file or the displayed messages for further information.

Your log file path is: vfs://root/pdf-compressor.log
";
        $output = $commandTester->getDisplay(true);
        $this->assertStringContainsString($expectedOutput, $output);

        $this->assertFileExists("{$this->getRoot()->url()}/pdf-compressor.log");

        $logContent = file_get_contents("{$this->getRoot()->url()}/pdf-compressor.log");

        for ($i = 0; $i < 5; $i++) {
            $this->assertFileDoesNotExist("{$this->getRoot()->url()}/docs/Original_PraticaCollaudata_$i.PDF");
            $this->assertFileExists("{$this->getRoot()->url()}/docs/PraticaCollaudata_$i.PDF");
            $this->assertStringContainsString(
                "INFO: Remove backup file `vfs://root/docs/Original_PraticaCollaudata_$i.PDF",
                $logContent
            );
        }
        $this->assertStringContainsString(
            "ERROR: Exception: Generic error",
            $logContent
        );
    }

    public function testCompressWithNoConfigFileThrowsException(): void
    {
        $this->expectException(PathException::class);
        $this->expectExceptionMessage('Unable to read the "vfs://root/.env" environment file.');

        $root = $this->getRoot();
        $root->removeChild('.env');

        $container = $this->getContainer();
    }
}
