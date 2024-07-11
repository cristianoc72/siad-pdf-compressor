<?php declare(strict_types=1);
/*
 * Copyright (c) Cristiano Cinotti 2021.
 *
 * This file is part of siad-pdf-compressor package, release under the APACHE-2 license.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Dotenv\Exception\PathException;

it("create a configuration file", function () {
    $this->populateFilesystem();
    $container = $this->getContainer();

    $app = $container->get('app');
    $command = $app->find('init');

    $commandTester = new CommandTester($command);
    $commandTester->setInputs([
        vfsStream::url('root/docs'),
        'ilovepdf_public_key',
        'ilovepdf_private_key',
        '/home/pdf-compressor.log',
        'Y'
    ]);

    $commandTester->execute([]);

    // test output
    $expectedOutput = 'Configuration file successfully created!
If you want to change it, please run `init` command again.
';
    $output = $commandTester->getDisplay(true);

    expect($commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and($output)->toContain($expectedOutput)
        ->and(file_get_contents(vfsStream::url('root/.env')))
            ->toContain("DOCS_DIR=" . vfsStream::url('root/docs'))
            ->toContain("PRIVATE_KEY=ilovepdf_private_key")
            ->toContain("PUBLIC_KEY=ilovepdf_public_key")
            ->toContain("LOG_FILE=/home/pdf-compressor.log")
            ->toContain("DISABLE_PREINVOICE=true")
    ;
});

it("display an errormessage if the document directory doesn't exist", function () {
    $this->populateFilesystem();
    $container = $this->getContainer();

    $app = $container->get('app');
    $command = $app->find('init');

    $commandTester = new CommandTester($command);
    $commandTester->setInputs([
        vfsStream::url('root/wrongPath'),
        vfsStream::url('root/docs'),
        'ilovepdf_public_key',
        'ilovepdf_private_key',
        'N'
    ]);
    $commandTester->execute([]);

    $output = $commandTester->getDisplay(true);
    expect($output)->toContain("Error! The document directory does not exists.");
});

it("throws an exception if the configuration file is not readable", function () {
    $this->populateWithUnreadableEnvFile();
    $container = $this->getContainer();

    $app = $container->get('app');
    $command = $app->find('init');

    $commandTester = new CommandTester($command);
    $commandTester->setInputs([
        vfsStream::url('root/docs'),
        'ilovepdf_public_key',
        'ilovepdf_private_key',
    ]);
    $commandTester->execute([]);
})->throws(PathException::class, 'Unable to read the "vfs://root/.env" environment file.');

it("displays an error if the configuration file is not writeable", function () {
    $this->populateWithNotWriteableEnvFile();
    $container = $this->getContainer();

    $app = $container->get('app');
    $command = $app->find('init');

    $commandTester = new CommandTester($command);
    $commandTester->setInputs([
        vfsStream::url('root/docs'),
        'ilovepdf_public_key',
        'ilovepdf_private_key'
    ]);
    $commandTester->execute([]);

    $output = $commandTester->getDisplay(true);

    expect($commandTester->getStatusCode())->toBe(Command::FAILURE)
        ->and($output)->toContain('Error! Impossible to write the file `vfs://root/.env`: do you have the correct permissions?');
});
