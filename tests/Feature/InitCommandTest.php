<?php declare(strict_types=1);
/*
 * Copyright (c) 2021 - 2025 Cristiano Cinotti
 *
 * This file is part of siad-pdf-compressor package, release under the APACHE-2 license.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

it("creates a configuration file", function () {
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
        'Y',
        "E_1\nE_2\nE_3"
    ]);

    $commandTester->execute([]);

    // test output
    $expectedOutput = 'Configuration file successfully created!
If you want to change it, please run `init` command again.
';
    $output = $commandTester->getDisplay(true);

    expect($commandTester->getStatusCode())->toBe(Command::SUCCESS)
        ->and($output)->toContain($expectedOutput)
        ->and(Yaml::parseFile(vfsStream::url('root/siad-pdf-compressor.yaml')))->toBe([
            'public_key' => 'ilovepdf_public_key',
            'private_key' => 'ilovepdf_private_key',
            'docs_dir' => vfsStream::url('root/docs'),
            'log_file' => '/home/pdf-compressor.log',
            'disable_preinvoice' => true,
            'excludes' => ['E_1', 'E_2', 'E_3']
        ]);
});

it("display an error message if the document directory doesn't exist", function () {
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
    $this->populateWithUnreadableConfigFile();
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
})->throws(ParseException::class, "File \"vfs://root/siad-pdf-compressor.yaml\" cannot be read.");

it("displays an error if the configuration file is not writeable", function () {
    $this->populateWithNotWriteableConfigFile();
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
        ->and($output)->toContain('Error! Impossible to write the file `vfs://root/siad-pdf-compressor.yaml`: do you have the correct permissions?');
});
