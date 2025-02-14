<?php declare(strict_types=1);
/*
 * Copyright (c) 2021 - 2025 Cristiano Cinotti
 *
 * This file is part of siad-pdf-compressor package, release under the APACHE-2 license.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

it("reverts a compression", function () {
    $this->populateFilesToRestore();
    $container = $this->getContainer();

    $app = $container->get('app');
    $command = $app->find('revert');

    $commandTester = new CommandTester($command);
    $commandTester->execute([]);

    $expectedOutput = '5 original documents successfully restored.
Please, see the log file for further information.

Your log file path is: vfs://root/pdf-compressor.log
';
    $output = $commandTester->getDisplay(true);

    expect($commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and($output)->toContain($expectedOutput)
        ->and("{$this->getRoot()->url()}/pdf-compressor.log")->toBeFile();

    $logContent = file_get_contents("{$this->getRoot()->url()}/pdf-compressor.log");

    for ($i = 0; $i < 5; $i++) {
        expect("{$this->getRoot()->url()}/docs/2024/PraticaCollaudata_$i.PDF")->toBeFile()
            ->and(filesize("{$this->getRoot()->url()}/docs/2024/PraticaCollaudata_$i.PDF"))->toBe(307200)
            ->and($logContent)->toContain("INFO: Reverted `vfs://root/docs/2024" . DIRECTORY_SEPARATOR . "Original_pratica_collaudata_$i.PDF` into `vfs://root/docs/2024" . DIRECTORY_SEPARATOR . "PraticaCollaudata_$i.PDF`")
        ;
    }
});

it("reverts uncompressed files", function () {
    $this->populateFilesystem();
    $app = $this->getContainer()->get('app');
    $command = $app->find('revert');

    $commandTester = new CommandTester($command);
    $commandTester->execute([]);

    $output = $commandTester->getDisplay(true);

    expect($commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and($output)->toContain('0 original documents successfully restored.')
        ->and("{$this->getRoot()->url()}/pdf-compressor.log")->not->toBeFile()
    ;
});

it("reverts not writeable files", function () {
    $this->populateNotWriteableFilesToRestore();
    $app = $this->getContainer()->get('app');
    $command = $app->find('revert');

    $commandTester = new CommandTester($command);
    $commandTester->execute([]);

    // test output
    $output = $commandTester->getDisplay(true);

    expect($commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($output)->toContain('Restore original documents executed with errors!')
        ->and("{$this->getRoot()->url()}/pdf-compressor.log")->toBeFile();

    $logContent = file_get_contents("{$this->getRoot()->url()}/pdf-compressor.log");

    for ($i = 0; $i < 5; $i++) {
        expect("{$this->getRoot()->url()}/docs/2024/Original_pratica_collaudata_$i.PDF")->toBeFile()
            ->and("{$this->getRoot()->url()}/docs/2024/PraticaCollaudata_$i.PDF")->toBeFile()
            ->and($logContent)->toContain(
                "ERROR: phootwork\\file\\exception\\FileException: Failed to move vfs://root/docs/2024" .
            DIRECTORY_SEPARATOR .
            "Original_pratica_collaudata_$i.PDF to vfs://root/docs/2024" . DIRECTORY_SEPARATOR . "PraticaCollaudata_$i.PDF"
            );
    }
});
