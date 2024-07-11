<?php declare(strict_types=1);
/*
 * Copyright (c) Cristiano Cinotti 2021.
 *
 * This file is part of siad-pdf-compressor package, release under the APACHE-2 license.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cristianoc72\PdfCompressor\Tests\Command;

use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Dotenv\Exception\PathException;

it("compresses the PDF files", function () {
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
    expect($output)->toContain($expectedOutput)
        ->and($commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and("{$this->getRoot()->url()}/pdf-compressor.log")->toBeFile();

    $logContent = file_get_contents("{$this->getRoot()->url()}/pdf-compressor.log");

    for ($i = 0; $i < 5; $i++) {
        expect("{$this->getRoot()->url()}/docs/2024" . DIRECTORY_SEPARATOR . "Original_pratica_collaudata_$i.PDF")->toBeFile()
            ->and($logContent)->toContain("INFO: Backup `vfs://root/docs/2024" . DIRECTORY_SEPARATOR . "PraticaCollaudata_$i.PDF` into `vfs://root/docs/2024" . DIRECTORY_SEPARATOR . "Original_pratica_collaudata_$i.PDF`.")
                ->toContain(
                    "INFO: `vfs://root/docs/2024" . DIRECTORY_SEPARATOR . "PraticaCollaudata_$i.PDF` compressed."
                );
    }
});

it("compresses the PDF files, adding pre-invoices files", function () {
    $this->populateForPreInvoice();
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
    expect($output)->toContain($expectedOutput)
        ->and($commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and("{$this->getRoot()->url()}/pdf-compressor.log")->toBeFile();

    for ($i = 0; $i < 5; $i++) {
        expect("{$this->getRoot()->url()}/docs/2024" .DIRECTORY_SEPARATOR . "E_$i" . DIRECTORY_SEPARATOR . "Original_pratica_collaudata_$i.PDF")->toBeFile()
            ->and("{$this->getRoot()->url()}/docs/2024/E_$i" . DIRECTORY_SEPARATOR . "E_$i.PDF")->toBeFile();
    }
});

it("try to compress not readable files", function () {
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
    expect($output)->toContain($expectedOutput)
        ->and("{$this->getRoot()->url()}/pdf-compressor.log")->toBeFile();

    $logContent = file_get_contents("{$this->getRoot()->url()}/pdf-compressor.log");

    for ($i = 0; $i < 4; $i++) {
        expect("{$this->getRoot()->url()}/docs/2024/Original_pratica_collaudata_$i.PDF")->toBeFile()
            ->and("{$this->getRoot()->url()}/docs/2024/PraticaCollaudata_$i.PDF")->toBeFile()
            ->and($logContent)->toContain("INFO: `vfs://root/docs/2024" . DIRECTORY_SEPARATOR . "PraticaCollaudata_$i.PDF` compressed.");
    }

    expect($logContent)->toContain("ERROR: phootwork\\file\\exception\\FileException: Failed to copy vfs://root/docs/2024" . DIRECTORY_SEPARATOR . "PraticaCollaudata_5.PDF to vfs://root/docs/2024" . DIRECTORY_SEPARATOR . "Original_pratica_collaudata_5.PDF")
        ->and("vfs://root/docs/2024/Original_pratica_collaudata_5.PDF")->not->toBeFile()
        ->and("vfs://root/docs/2024/PraticaCollaudata_5.PDF")->toBeFile();
});

it("stops with a failure when authentication error", function () {
    $this->populateFilesystem();
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

Your log file path is: vfs://root/pdf-compressor.log
";
    $output = $commandTester->getDisplay(true);

    expect($commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($output)->toContain($expectedOutput)
        ->and("{$this->getRoot()->url()}/pdf-compressor.log")->toBeFile();

    $logContent = file_get_contents("{$this->getRoot()->url()}/pdf-compressor.log");

    for ($i = 0; $i < 5; $i++) {
        expect("{$this->getRoot()->url()}/docs/2024/Original_pratica_collaudata_$i.PDF")->not->toBeFile()
            ->and("{$this->getRoot()->url()}/docs/2024/PraticaCollaudata_$i.PDF")->toBeFile()
            ->and($logContent)->toContain("INFO: Remove backup file `vfs://root/docs/2024" . DIRECTORY_SEPARATOR . "Original_pratica_collaudata_$i.PDF");
    }

    expect($logContent)->toContain("ERROR: Ilovepdf\Exceptions\AuthException: Invalid credentials");
});

it("stops with a failure when download error", function () {
    $this->populateFilesystem();
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

Your log file path is: vfs://root/pdf-compressor.log
";
    $output = $commandTester->getDisplay(true);

    expect($commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($output)->toContain($expectedOutput)
        ->and("{$this->getRoot()->url()}/pdf-compressor.log")->toBeFile();

    $logContent = file_get_contents("{$this->getRoot()->url()}/pdf-compressor.log");

    for ($i = 0; $i < 5; $i++) {
        expect("{$this->getRoot()->url()}/docs/2024/Original_pratica_collaudata_$i.PDF")->not->toBeFile()
            ->and("{$this->getRoot()->url()}/docs/2024/PraticaCollaudata_$i.PDF")->toBeFile()
            ->and($logContent)->toContain("INFO: Remove backup file `vfs://root/docs/2024" . DIRECTORY_SEPARATOR . "Original_pratica_collaudata_$i.PDF");
    }

    expect($logContent)->toContain("ERROR: Ilovepdf\Exceptions\DownloadException: Download error");
});

it("stops with a failure when generic error", function () {
    $this->populateFilesystem();
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

Your log file path is: vfs://root/pdf-compressor.log
";
    $output = $commandTester->getDisplay(true);

    expect($commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($output)->toContain($expectedOutput)
        ->and("{$this->getRoot()->url()}/pdf-compressor.log")->toBeFile();

    $logContent = file_get_contents("{$this->getRoot()->url()}/pdf-compressor.log");

    for ($i = 0; $i < 5; $i++) {
        expect("{$this->getRoot()->url()}/docs/2024/Original_pratica_collaudata_$i.PDF")->not->toBeFile()
            ->and("{$this->getRoot()->url()}/docs/2024/PraticaCollaudata_$i.PDF")->toBeFile()
            ->and($logContent)->toContain("INFO: Remove backup file `vfs://root/docs/2024" . DIRECTORY_SEPARATOR . "Original_pratica_collaudata_$i.PDF");
    }

    expect($logContent)->toContain("ERROR: Exception: Generic error");
});

it("can't find the configuration file", function () {
    $this->getRoot()->removeChild('.env');
    $container = $this->getContainer();
})->throws(PathException::class, 'Unable to read the "vfs://root/.env" environment file.');

it("searches files also in previous year folder", function () {
    $this->populateWithPreviousYear();
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
    expect($output)->toContain($expectedOutput)
        ->and($commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and("{$this->getRoot()->url()}/pdf-compressor.log")->toBeFile();

    $logContent = file_get_contents("{$this->getRoot()->url()}/pdf-compressor.log");

    for ($i = 0; $i < 5; $i++) {
        expect("{$this->getRoot()->url()}/docs/2024" . DIRECTORY_SEPARATOR . "Original_pratica_collaudata_$i.PDF")->toBeFile()
            ->and($logContent)->toContain("INFO: Backup `vfs://root/docs/2024" . DIRECTORY_SEPARATOR . "PraticaCollaudata_$i.PDF` into `vfs://root/docs/2024" . DIRECTORY_SEPARATOR . "Original_pratica_collaudata_$i.PDF`.")
                ->toContain(
                    "INFO: `vfs://root/docs/2024" . DIRECTORY_SEPARATOR . "PraticaCollaudata_$i.PDF` compressed."
                );
    }

    for ($i = 5; $i < 10; $i++) {
        expect("{$this->getRoot()->url()}/docs/2023" . DIRECTORY_SEPARATOR . "Original_pratica_collaudata_$i.PDF")->toBeFile()
            ->and($logContent)->toContain("INFO: Backup `vfs://root/docs/2023" . DIRECTORY_SEPARATOR . "PraticaCollaudata_$i.PDF` into `vfs://root/docs/2023" . DIRECTORY_SEPARATOR . "Original_pratica_collaudata_$i.PDF`.")
                ->toContain(
                    "INFO: `vfs://root/docs/2023" . DIRECTORY_SEPARATOR . "PraticaCollaudata_$i.PDF` compressed."
                );
    }
});
