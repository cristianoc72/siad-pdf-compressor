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
use phootwork\lang\Text;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class RevertCommandTest extends TestCase
{
    public function testRevert(): void
    {
        $this->populateFilesToRestore();
        $container = $this->getContainer();

        $app = $container->get('app');
        $command = $app->find('revert');

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());

        // test output
        $expectedOutput = '5 original documents successfully restored.
Please, see the log file for further information.

Your log file path is: vfs://root/pdf-compressor.log
';
        $output = $commandTester->getDisplay(true);
        $this->assertStringContainsString($expectedOutput, $output);

        $this->assertFileExists("{$this->getRoot()->url()}/pdf-compressor.log");

        $logContent = file_get_contents("{$this->getRoot()->url()}/pdf-compressor.log");

        for ($i = 0; $i < 5; $i++) {
            $this->assertFileExists("{$this->getRoot()->url()}/docs/PraticaCollaudata_$i.PDF");
            $this->assertEquals(307200, filesize("{$this->getRoot()->url()}/docs/PraticaCollaudata_$i.PDF"));
            $this->assertStringContainsString(
                "INFO: Reverted `vfs://root/docs" . DIRECTORY_SEPARATOR . "Original_PraticaCollaudata_$i.PDF` into `vfs://root/docs" . DIRECTORY_SEPARATOR . "PraticaCollaudata_$i.PDF`",
                $logContent
            );
        }
    }

    public function testRevertUncompressedFiles(): void
    {
        $this->populateFilesystem();
        $app = $this->getContainer()->get('app');
        $command = $app->find('revert');

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());

        // test output
        $output = $commandTester->getDisplay(true);
        $this->assertStringContainsString('0 original documents successfully restored.', $output);

        $this->assertFileDoesNotExist("{$this->getRoot()->url()}/pdf-compressor.log");
    }

    public function testRevertNotWriteableFiles(): void
    {
        $this->populateNotWriteableFilesToRestore();
        $app = $this->getContainer()->get('app');
        $command = $app->find('revert');

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());

        // test output
        $output = $commandTester->getDisplay(true);
        $this->assertStringContainsString('Restore original documents executed with errors!', $output);

        $this->assertFileExists("{$this->getRoot()->url()}/pdf-compressor.log");
        $logContent = file_get_contents("{$this->getRoot()->url()}/pdf-compressor.log");

        for ($i = 0; $i < 5; $i++) {
            $this->assertFileExists("{$this->getRoot()->url()}/docs/Original_PraticaCollaudata_$i.PDF");
            $this->assertFileExists("{$this->getRoot()->url()}/docs/PraticaCollaudata_$i.PDF");
            $this->assertStringContainsString(
                "ERROR: phootwork\\file\\exception\\FileException: Failed to move vfs://root/docs" .
                DIRECTORY_SEPARATOR .
                "Original_PraticaCollaudata_$i.PDF to vfs://root/docs" . DIRECTORY_SEPARATOR . "PraticaCollaudata_$i.PDF",
                $logContent
            );
        }
    }
}
