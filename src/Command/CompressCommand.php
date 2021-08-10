<?php declare(strict_types=1);
/*
 * Copyright (c) Cristiano Cinotti 2021.
 *
 * This file is part of siad-pdf-compressor package, release under the APACHE-2 license.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cristianoc72\PdfCompressor\Command;

use cristianoc72\PdfCompressor\Configuration;
use Exception;
use Ilovepdf\CompressTask;
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
    private Configuration $configuration;
    private bool $errors = false;

    public function __construct(Finder $finder, CompressTask $iLovePdf, Logger $logger, Configuration $configuration)
    {
        $this->finder = $finder;
        $this->iLovePdf = $iLovePdf;
        $this->logger = $logger;
        $this->configuration = $configuration;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription("Compress all the PDF into the given directory")
            ->addOption('dir', null, InputArgument::REQUIRED, 'The directory containing the pdf files to compress.')
            ->addOption('public-key', null, InputArgument::REQUIRED, 'IlovePdf public key.')
            ->addOption('private-key', null, InputArgument::REQUIRED, 'IlovePdf private key.')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (!empty($input->getOption('dir'))) {
            $this->configuration->setDocsDir($input->getOption('dir'));
        }
        if (!empty($input->getOption('public-key'))) {
            $this->configuration->setPublicKey($input->getOption('public-key'));
        }
        if (!empty($input->getOption('private-key'))) {
            $this->configuration->setPublicKey($input->getOption('private-key'));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->finder->in($this->configuration->getDocsDir())
                ->name('PraticaCo*.PDF')->name('PraticaCo*.pdf')
                ->size('> 200k')
                ->files();

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
                } catch (Exception $exception) {
                    $this->showError($exception, $output);
                    $backupFile->delete();
                    $this->logger->info("Remove backup file `{$backupFile->getPathname()}`.");
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
</info>";
            $output->writeln($message);
            $output->writeln("Your log file path is: {$this->configuration->getDocsDir()}/pdf-compressor.log");
        } catch (Exception $e) {
            $output->writeln('<error>' . get_class($e) . '</error>');
            $output->writeln("<error>{$e->getMessage()}</error>");

            return Command::FAILURE;
        }

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
