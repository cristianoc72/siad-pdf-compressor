<?php declare(strict_types=1);

require 'vendor/autoload.php';

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load($_SERVER['HOME'] . '/.env');

$finder = new \Symfony\Component\Finder\Finder();
$finder->in(getenv('DOCS_DIR'))->name('Pratica*.PDF')->size('< 200k')->files();

foreach ($finder as $file) {
    unlink($file->getRealPath());
}

$finder1 = new \Symfony\Component\Finder\Finder();
$finder1->in(getenv('DOCS_DIR'))->name('Original_Pratica*.PDF')->size('> 200k')->files();

foreach ($finder1 as $file) {
    $newName = str_replace('Original_', '', $file->getRealPath());
    rename($file->getRealPath(), $newName);
}
