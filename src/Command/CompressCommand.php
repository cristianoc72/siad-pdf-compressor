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
use Ilovepdf\Ilovepdf;
use Monolog\Logger;
use phootwork\file\exception\FileException;
use phootwork\file\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use function PHPUnit\Framework\directoryExists;

class CompressCommand extends BaseCommand
{
    protected static $defaultName = 'compress';
    protected Ilovepdf $iLovePdf;

    public function __construct(Finder $finder, Logger $logger, Configuration $configuration, Ilovepdf $iLovePdf)
    {
        $this->iLovePdf = $iLovePdf;

        parent::__construct($finder, $logger, $configuration);
    }

    protected function configure(): void
    {
        $this
            ->setDescription("Compress all the PDF into the given directory")
            ->addOption('public-key', null, InputArgument::REQUIRED, 'IlovePdf public key.')
            ->addOption('private-key', null, InputArgument::REQUIRED, 'IlovePdf private key.')
            ->addOption('log-file', null, InputArgument::REQUIRED, 'Log file')
        ;

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->finder->in($this->configuration->getDocsDir());
            if ('' !== $previousDir = $this->getPreviousDocsDir()) {
                $this->finder->in($previousDir);
            }

            $this->finder->name('PraticaCo*.PDF')->name('PraticaCo*.pdf')
                ->size('> 200k')
                ->files();

            $progress = new ProgressBar($output, $this->finder->count());
            $progress->start();

            foreach ($this->finder as $fileInfo) {
                try {
                    $file = new File($fileInfo->getPathname());

                    //Original file backup
                    $backupFile = new File($file->getDirname()->ensureEnd(DIRECTORY_SEPARATOR)->append("Original_")->append($file->getFilename()));
                    $file->copy($backupFile->toPath());
                    $this->logger->info("Backup `{$file->getPathname()}` into `{$backupFile->getPathname()}`.");

                   //Compress file
                    $task = $this->iLovePdf->newTask('compress');
                    $task->addFile($file->getPathname()->toString());
                    $task->setOutputFilename($file->getFilename()->toString());
                    $task->setCompressionLevel('extreme');
                    $task->execute();
                    $task->download($file->getDirname()->toString());

                    $this->logger->info("`{$file->getPathname()}` compressed.");

                    $progress->advance();
                } catch (FileException $fileException) {
                    $this->showError($fileException, $output);
                } catch (Exception $exception) {
                    $this->showError($exception, $output);
                    if (isset($backupFile)) {
                        $backupFile->delete();
                        $this->logger->info("Remove backup file `{$backupFile->getPathname()}`.");
                    }
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
            $output->writeln("Your log file path is: {$this->configuration->getLogFile()}");
        } catch (Exception $e) {
            $output->writeln('<error>' . get_class($e) . '</error>');
            $output->writeln("<error>{$e->getMessage()}</error>");

            return Command::FAILURE;
        }

        return $this->errors ? Command::FAILURE : Command::SUCCESS;
    }

    private function getPreviousDocsDir(): string
    {
        $dir = $this->configuration->getDocsDir();
        $year = substr($dir, -4);
        $prevDir = str_replace($year, (string) ((int) $year - 1), $dir);

        return file_exists($prevDir) ? $prevDir : '';
    }
}
