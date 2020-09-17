<?php declare(strict_types=1);
/**
 * Copyright (c) 2020 Cristiano Cinotti
 *
 * This file is part of siad-pdf-compressor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license Apache-2.0
 */

namespace cristianoc72\PdfCompressor\Command;


use DateTimeImmutable;
use Exception;
use Ilovepdf\CompressTask;
use Ilovepdf\Exceptions\AuthException;
use Ilovepdf\Exceptions\ProcessException;
use Ilovepdf\Exceptions\UploadException;
use Monolog\Logger;
use phootwork\file\exception\FileException;
use phootwork\file\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class CompressCommand extends Command
{
    protected static $defaultName = 'compress';

    private Finder $finder;
    private CompressTask $iLovePdf;
    private Logger $logger;
    private bool $errors = false;

    public function __construct(Finder $finder, CompressTask $iLovePdf, Logger $logger)
    {
        $this->finder = $finder;
        $this->iLovePdf = $iLovePdf;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription("Compress all the PDF into the given directory")
            ->addOption('dir', null, InputArgument::REQUIRED, 'The directory containing the pdf files to compress.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dir = $input->getOption('dir') ?? getenv('DOCS_DIR');
        $this->finder->in($dir)->name('PraticaCo*.PDF')->name('PraticaCo*.pdf')->size('> 200k')->files();
        $date = new DateTimeImmutable();
        $this->logger->info("{$date->format('Y-m-d H:i:s')} Compress PDF documents\n");

        $progress = new ProgressBar($output, $this->finder->count());
        $progress->start();

        foreach ($this->finder as $fileInfo) {
            try {
                $file = new File($fileInfo->getPathname());

                //Original file backup
                $backupFile = new File($file->getDirname()->ensureEnd('/')->append("Original_")->append($file->getFilename()));
                $file->copy($backupFile->toPath());
                $this->logger->info("Backup `{$file->getPathname()}` into `{$backupFile->getPathname()}`.");

                //Compress file
                $this->iLovePdf->addFile($file->getPathname());
                $this->iLovePdf->setOutputFilename($file->getFilename());
                $this->iLovePdf->setCompressionLevel('extreme');
                $this->iLovePdf->execute();
                $this->iLovePdf->download($file->getDirname());

                $this->logger->info("`{$file->getPathname()}` compressed.");

                $progress->advance();
            } catch (FileException $fileException) {
                $this->showError($fileException, $output);
            } catch (AuthException $authException) {
                $this->showError($authException, $output);
                die();
            } catch (ProcessException $processException) {
                $this->showError($processException, $output);
            } catch (UploadException $uploadException) {
                $this->showError($uploadException, $output);
            } catch (Exception $exception) {
                $this->showError($exception, $output);
                die();
            }
        }

        $progress->finish();

        $message = $this->errors ? "
<error>Compression executed with errors!

Please, see the log file or the displayed messages for further information.
</error>"
            : "
<info>Compression successfully executed!

Please, see the log file for further information.
</info>"
            ;
        $output->writeln($message);
        $output->writeln("Your log file path is: " . getenv('DOCS_DIR') . "/pdf-compressor.log");

        return $this->errors ? Command::FAILURE : Command::SUCCESS;
    }

    private function showError(Exception $exception, OutputInterface $output): void
    {
        $message = get_class($exception) . ": {$exception->getMessage()}";
        $this->logger->error($message);
        $output->writeln("<error>$message</error>");
        $this->errors = true;
    }
}
