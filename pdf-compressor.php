<?php declare(strict_types=1);
/*
 * Copyright (c) Cristiano Cinotti 2021.
 *
 * This file is part of siad-pdf-compressor package, release under the APACHE-2 license.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use cristianoc72\PdfCompressor\Container;
use phootwork\file\File;
use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Exception\PathException;

require 'vendor/autoload.php';

try {
    $container = new Container();
    /** @var Application $application */
    $application = $container->get('app');
    $application->run();
} catch (PathException $e) {
    echo "
Configuration file not found!
Please run `init` command to initialize the application:

    php pdf-compressor.phar init

";
    $file = new File($_SERVER['HOME'] . '/.env');
    $file->write("
DOCS_DIR=''
PRIVATE_KEY=''
PUBLIC_KEY=''
LOG_FILE=''
");
} catch (Exception $e) {
    echo "ERROR: " . get_class($e) . " {$e->getMessage()}\n";
    echo "Please, run `init` command to configure the application.\n\n";
}
