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

class InitCommandTest extends TestCase
{
    public function testInit(): void
    {
        $this->populateFilesystem();
        $container = $this->getContainer();

        $app = $container->get('app');
        $command = $app->find('init');

        $commandTester = new CommandTester($command);
        $commandTester->setInputs([
            vfsStream::url('root/docs'),
            'ilovepdf_public_key',
            'ilovepdf_private_key',
            '/home/pdf-compressor.log'
        ]);

        $commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());

        // test output
        $expectedOutput = 'Configuration file successfully created!
If you want to change it, please run `init` command again.
';
        $output = $commandTester->getDisplay(true);
        $this->assertStringContainsString($expectedOutput, $output);

        // test configuration file
        $dotenvContent = file_get_contents(vfsStream::url('root/.env'));

        $this->assertStringContainsString("DOCS_DIR=" . vfsStream::url('root/docs'), $dotenvContent);
        $this->assertStringContainsString("PRIVATE_KEY=ilovepdf_private_key", $dotenvContent);
        $this->assertStringContainsString("PUBLIC_KEY=ilovepdf_public_key", $dotenvContent);
        $this->assertStringContainsString("LOG_FILE=/home/pdf-compressor.log", $dotenvContent);
    }

    public function testInitWrongDirectory(): void
    {
        $this->populateFilesystem();
        $container = $this->getContainer();

        $app = $container->get('app');
        $command = $app->find('init');

        $commandTester = new CommandTester($command);
        $commandTester->setInputs([
            vfsStream::url('root/wrongPath'),
            vfsStream::url('root/docs'),
            'ilovepdf_public_key',
            'ilovepdf_private_key'
        ]);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay(true);
        $this->assertStringContainsString("Error! The document directory does not exists.", $output);
    }

    public function testInitNotReadableFileThrowsException(): void
    {
        $this->expectException(PathException::class);
        $this->expectExceptionMessage('Unable to read the "vfs://root/.env" environment file.');

        $this->populateWithUnreadableEnvFile();
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
    }

    public function testInitNotWriteableFileThrowsException(): void
    {
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

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay(true);
        $this->assertStringContainsString(
            'Error! Impossible to write the file `vfs://root/.env`: do you have the correct permissions?',
            $output
        );
    }
}
