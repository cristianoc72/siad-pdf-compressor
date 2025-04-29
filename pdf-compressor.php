<?php declare(strict_types=1);
/*
 * Copyright (c) 2021 - 2025 Cristiano Cinotti
 *
 * This file is part of siad-pdf-compressor package, release under the APACHE-2 license.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use cristianoc72\PdfCompressor\Container;
use phootwork\file\File;
use Symfony\Component\Console\Application;
use Symfony\Component\Yaml\Exception\ParseException;

require 'vendor/autoload.php';

try {
    $container = new Container();
    /** @var Application $application */
    $application = $container->get('app');
    $application->run();
} catch (ParseException $e) {
    echo "
Configuration file not found!
Please run `init` command to initialize the application:

    php pdf-compressor.phar init

";
    $file = new File($_SERVER['HOME'] . '/siad-pdf-compressor.yaml');
    $file->write("
docs_dir: ~
private_key: ~
public_key: ~
log_file: ~
");
} catch (Exception $e) {
    echo "ERROR: " . get_class($e) . " {$e->getMessage()}\n";
    echo "Please, run `init` command to configure the application.\n\n";
}
