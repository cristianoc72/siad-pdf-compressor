<?php declare(strict_types=1);
/*
 * Copyright (c) Cristiano Cinotti 2021.
 *
 * This file is part of siad-pdf-compressor package, release under the APACHE-2 license.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Tester\CommandTester;

beforeEach(function () {
    $this->populateFilesystem();
});

it("prepares the setter methods", function () {
    $container = $this->getContainer();

    $app = $container->get('app');
    $command = $app->find('compress');
    $commandTester = new CommandTester($command);
    $commandTester->execute([
        '--docs-dir' => 'vfs://root/my/awesome/dir',
        '--public-key' => 'my_public_key',
        '--private-key' => 'my_private_key'
    ]);

    $output = $commandTester->getDisplay(true);

    expect($output)->toContain('Symfony\Component\Finder\Exception\DirectoryNotFoundException
The "vfs://root/my/awesome/dir" directory does not exist.
')->and("{$this->getRoot()->url()}/pdf-compressor.log")->not->toBeFile();

    for ($i = 0; $i < 5; $i++) {
        expect("{$this->getRoot()->url()}/docs/PraticaCollaudata_$i.PDF")->toBeFile()
            ->and("{$this->getRoot()->url()}/docs/Original_PraticaCollaudata_$i.PDF")->not->toBeFile();
    }

    expect($container->get('configuration')->getPublicKey())->toBe('my_public_key')
        ->and($container->get('configuration')->getPrivateKey())->toBe('my_private_key');
});
