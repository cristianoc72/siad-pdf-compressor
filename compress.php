<?php declare(strict_types=1);

require 'vendor/autoload.php';

$logger = new \Monolog\Logger('Siad pdf compressor');
$logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/pdf-compressor.log'));

$finder = new \Symfony\Component\Finder\Finder();
$finder->in(__DIR__ . '/DocBARGHINI/2019')->name('Pratica*.PDF')->size('> 200k')->files();

$counter = 0;
foreach ($finder as $fileInfo) {
    //Original file backup
    if (!copy($fileInfo->getRealPath(), "{$fileInfo->getPath()}/Original_{$fileInfo->getFilename()}")) {
        $logger->error("Error while copying '{$fileInfo->getRealPath()}' into '{$fileInfo->getPath()}/Original_{$fileInfo->getFilename()}'.");
        die ("Impossibile effettuare il backup del file {$fileInfo->getRealPath()}.");
    }
    $logger->info("Backup '{$fileInfo->getRealPath()}' into '{$fileInfo->getPath()}/Original_{$fileInfo->getFilename()}'.");

    try {
        //Compress file
        $myTask = new \Ilovepdf\CompressTask('public key', 'private key');
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
