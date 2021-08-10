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
use org\bovigo\vfs\content\LargeFileContent;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Tester\CommandTester;

class CompressCommandTest extends TestCase
{
    public function testInteract(): void
    {
        $container = $this->getContainer();

        $app = $container->get('app');
        $command = $app->find('compress');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--dir' => 'vfs://root/my/awesome/dir',
            '--public-key' => 'my_public_key',
            '--private-key' => 'my_private_key'
        ]);

        // test output
        $expectedOutput = 'Symfony\Component\Finder\Exception\DirectoryNotFoundException
The "vfs://root/my/awesome/dir" directory does not exist.
';
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString($expectedOutput, $output);

        $this->assertFileDoesNotExist("{$this->getRoot()->url()}/docs/pdf-compressor.log");

        for ($i = 0; $i < 5; $i++) {
            $this->assertFileExists("{$this->getRoot()->url()}/docs/PraticaCollaudata_$i.PDF");
            $this->assertFileDoesNotExist("{$this->getRoot()->url()}/docs/Original_PraticaCollaudata_$i.PDF");
        }
    }

    public function testCompress(): void
    {
        $container = $this->getContainer();

        $app = $container->get('app');
        $command = $app->find('compress');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // test output
        $expectedOutput = "
Compression successfully executed!

Please, see the log file for further information.

Your log file path is: vfs://root/docs/pdf-compressor.log
";
        $expectedProgressBar = " 0/5 [>---------------------------]   0%
 1/5 [=====>----------------------]  20%
 2/5 [===========>----------------]  40%
 3/5 [================>-----------]  60%
 4/5 [======================>-----]  80%
 5/5 [============================] 100%
";
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString($expectedOutput, $output);
        $this->assertStringContainsString($expectedProgressBar, $output);

        $this->assertFileExists("{$this->getRoot()->url()}/docs/pdf-compressor.log");

        $logContent = file_get_contents("{$this->getRoot()->url()}/docs/pdf-compressor.log");

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
        $this->populateWithNotReadableFile();

        $container = $this->getContainer();

        $app = $container->get('app');
        $command = $app->find('compress');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // test output
        $expectedOutput = "
Compression executed with errors!

Please, see the log file or the displayed messages for further information.

Your log file path is: vfs://root/docs/pdf-compressor.log
";
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString($expectedOutput, $output);

        $this->assertFileExists("{$this->getRoot()->url()}/docs/pdf-compressor.log");

        $logContent = file_get_contents("{$this->getRoot()->url()}/docs/pdf-compressor.log");

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
        $container = $this->getContainer();
        $container->set('iLovePdf', $this->getIlovePdfWithAuthException());

        $app = $container->get('app');
        $command = $app->find('compress');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // test output
        $expectedOutput = "
Compression executed with errors!

Please, see the log file or the displayed messages for further information.

Your log file path is: vfs://root/docs/pdf-compressor.log
";
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString($expectedOutput, $output);

        $this->assertFileExists("{$this->getRoot()->url()}/docs/pdf-compressor.log");

        $logContent = file_get_contents("{$this->getRoot()->url()}/docs/pdf-compressor.log");

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
        $container = $this->getContainer();
        $container->set('iLovePdf', $this->getIlovePdfWithDownloadException());

        $app = $container->get('app');
        $command = $app->find('compress');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // test output
        $expectedOutput = "
Compression executed with errors!

Please, see the log file or the displayed messages for further information.

Your log file path is: vfs://root/docs/pdf-compressor.log
";
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString($expectedOutput, $output);

        $this->assertFileExists("{$this->getRoot()->url()}/docs/pdf-compressor.log");

        $logContent = file_get_contents("{$this->getRoot()->url()}/docs/pdf-compressor.log");

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
        $container = $this->getContainer();
        $container->set('iLovePdf', $this->getIlovePdfWithException());

        $app = $container->get('app');
        $command = $app->find('compress');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // test output
        $expectedOutput = "
Compression executed with errors!

Please, see the log file or the displayed messages for further information.

Your log file path is: vfs://root/docs/pdf-compressor.log
";
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString($expectedOutput, $output);

        $this->assertFileExists("{$this->getRoot()->url()}/docs/pdf-compressor.log");

        $logContent = file_get_contents("{$this->getRoot()->url()}/docs/pdf-compressor.log");

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

    private function populateWithNotReadableFile(): void
    {
        $docsDir = vfsStream::newDirectory('docs')->at($this->getRoot());
        $dotEnv = vfsStream::newFile('.env')->at($this->getRoot())->setContent(
            "
PUBLIC_KEY=public_key
PRIVATE_KEY=private_key
DOCS_DIR={$docsDir->url()}
"
        );
        for ($i = 0; $i < 5; $i++) {
            $doc = vfsStream::newFile("PraticaCollaudata_$i.PDF")
                ->at($docsDir)->withContent(LargeFileContent::withKilobytes(500))
            ;
        }

        vfsStream::newFile("PraticaCollaudata_5.PDF")
            ->at($docsDir)->withContent(LargeFileContent::withKilobytes(500))->chmod(000)
        ;
    }
}
