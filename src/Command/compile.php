<?php declare(strict_types=1);
/**
 * Many thanks to Daniel Opitz
 * https://odan.github.io/2017/08/16/create-a-php-phar-file.html
 *
 * It creates the phar for this library.
 */
require 'vendor/autoload.php';

// The php.ini setting phar.readonly must be set to 0
$pharFile = 'compressor.phar';

// clean up
if (file_exists($pharFile)) {
    unlink($pharFile);
}
if (file_exists($pharFile . '.gz')) {
    unlink($pharFile . '.gz');
}

// create phar
$p = new Phar($pharFile);

$excluded = [
    '.env*',
    '.gitignore',
    'compile.php',
    'composer.*',
    '.git',
    '.idea'
];
$finder = new \Symfony\Component\Finder\Finder();
$finder->in(__DIR__)->notName($excluded)->files();
// creating our library using whole directory
$p->buildFromIterator($finder, __DIR__);

// pointing main file which requires all classes
$p->setDefaultStub('compress.php', 'compress.php');

// plus - compressing it into gzip
$p->compress(Phar::GZ);

echo "$pharFile successfully created";
