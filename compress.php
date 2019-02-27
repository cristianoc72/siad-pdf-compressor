<?php declare(strict_types=1);
/**
 * Compress Siad PDF documents to create electronic invoices.
 * It uses IlovePdf library.
 */

require 'vendor/autoload.php';

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load($_SERVER['HOME'] . '/.env');

$logger = new \Monolog\Logger('Siad pdf compressor');
$logger->pushHandler(new \Monolog\Handler\StreamHandler(getenv('DOCS_DIR') . '/pdf-compressor.log'));

$finder = new \Symfony\Component\Finder\Finder();
$finder->in(getenv('DOCS_DIR'))->name('Pratica*.PDF')->size('> 200k')->files();

$counter = 0;
foreach ($finder as $fileInfo) {
    $name = "{$fileInfo->getPath()}/Original_document.pdf";
    $i = 0;
    while(file_exists($name)) {
        $i++;
        $name = "{$fileInfo->getPath()}/Original_document($i).pdf";
    }

    //Original file backup
    if (!copy($fileInfo->getRealPath(), $name)) {
        $logger->error("Error while copying '{$fileInfo->getRealPath()}' into '$name'.");
        die ("Impossibile effettuare il backup del file {$fileInfo->getRealPath()}.");
    }
    $logger->info("Backup '{$fileInfo->getRealPath()}' into '$name'.");

    try {
        //Compress file
        $myTask = new \Ilovepdf\CompressTask(getenv('PUBLIC_KEY'), getenv('PRIVATE_KEY'));
        $myTask->addFile($fileInfo->getRealPath());
        $myTask->setOutputFilename($fileInfo->getFilename());
        $myTask->setCompressionLevel('extreme');
        $myTask->execute();
        $myTask->download($fileInfo->getPath());

        $message = "'{$fileInfo->getRealPath()}' compressed.";
        echo "$message\n";
        $logger->info($message);
        $counter++;
    } catch (Exception $exception) {
        $class = get_class($exception);
        $logger->error("$class: {$exception->getMessage()}");
        die("$class: {$exception->getMessage()}");
    }
}

echo "Procedure di compressione terminata con successo!!\nProcessati $counter files\nGuarda il file di log per i dettagli";
