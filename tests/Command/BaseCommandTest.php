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
use Symfony\Component\Console\Tester\CommandTester;

class BaseCommandTest extends TestCase
{
    public function testInteract(): void
    {
        $this->populateFilesystem();
        $container = $this->getContainer();

        $app = $container->get('app');
        $command = $app->find('compress');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--docs-dir' => 'vfs://root/my/awesome/dir',
            '--public-key' => 'my_public_key',
            '--private-key' => 'my_private_key'
        ]);

        // test output
        $expectedOutput = 'Symfony\Component\Finder\Exception\DirectoryNotFoundException
The "vfs://root/my/awesome/dir" directory does not exist.
';
        $output = $commandTester->getDisplay(true);
        $this->assertStringContainsString($expectedOutput, $output);

        $this->assertFileDoesNotExist("{$this->getRoot()->url()}/pdf-compressor.log");

        for ($i = 0; $i < 5; $i++) {
            $this->assertFileExists("{$this->getRoot()->url()}/docs/PraticaCollaudata_$i.PDF");
            $this->assertFileDoesNotExist("{$this->getRoot()->url()}/docs/Original_PraticaCollaudata_$i.PDF");
        }

        $this->assertEquals('my_public_key', $container->get('configuration')->getPublicKey());
        $this->assertEquals('my_private_key', $container->get('configuration')->getPrivateKey());
    }
}
