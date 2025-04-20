<?php declare(strict_types=1);
/**
 * Copyright (c) 2021 - 2025 Cristiano Cinotti
 *
 * This file is part of siad-pdf-compressor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license Apache-2.0
 */

namespace cristianoc72\PdfCompressor\Command;

use Exception;
use phootwork\file\exception\FileException;
use phootwork\file\File;
use phootwork\file\Path;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Restore the uncompressed documents.
 */
#[AsCommand(name: 'revert')]
class RevertCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->setDescription("Revert the compressed documents to the original state.")
            ->addArgument('dirs', InputArgument::IS_ARRAY, 'The directories where the documents to revert reside (separate multiple directories with a space).')
            ->addOption('log-file', null, InputArgument::REQUIRED, 'Log file')
        ;

        parent::configure();
    }

    /**
     * Revert the compressed documents.
     * If no directory given, it reverts all the compressed files into the configured documents directory.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dirs = $input->getArgument('dirs');
        if (count($dirs) <= 0) {
            $dirs[] = $this->configuration->get('docs_dir');
        }
        $dirs = $this->makeAbsolute($dirs);

        foreach ($dirs as $dir) {
            $this->finder->in($dir);
        }

        $this->finder->name('Original_*')->files();

        $foundFiles = $this->finder->count();

        $progress = new ProgressBar($output, $foundFiles);
        $progress->start();

        /** @var SplFileInfo $fileInfo */
        foreach ($this->finder as $fileInfo) {
            try {
                $this->revertFile($fileInfo);
                $progress->advance();
            } catch (Exception $exception) {
                $this->showError($exception, $output);
            }
        }

        $progress->finish();

        $message = $this->errors ? "
<error>Restore original documents executed with errors!

Please, see the log file or the displayed messages for further information.
</error>"
            : "

<info>$foundFiles original documents successfully restored.
Please, see the log file for further information.
</info>";
        $output->writeln($message);
        $output->writeln("Your log file path is: {$this->configuration->get('log_file')}");

        return $this->errors ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * @throws FileException
     */
    private function revertFile(SplFileInfo $fileInfo): void
    {
        $file = new File($fileInfo->getPathname());
        $fileName = $file->getFilename();
        $affix = $fileName->substring($fileName->lastIndexOf('_') ?? $fileName->length());
        $revertName = $fileName
            ->replace('Original_', '')
            ->replace($affix, '')
            ->toStudlyCase()
            ->append($affix)
            ->prepend($file->getDirname()->ensureEnd(DIRECTORY_SEPARATOR))
        ;
        $file->move($revertName);

        $this->logger->info("Reverted `{$fileInfo->getPathname()}` into `{$revertName}`.");
    }

    /**
     * Make the directorie absolute.
     * If a directory is not absolute, it's considered relative to configured docuemnts dir.
     *
     * @param string[] $dirs
     * @return string[]
     */
    private function makeAbsolute(array $dirs): array
    {
        return array_map(
            function (string $elem): string {
                $path = new Path($elem);
                if (!$path->isAbsolute()) {
                    $path = new Path($path->getPathName()->ensureStart("/")->prepend($this->configuration->get('docs_dir')));
                }

                return $path->toString();
            },
            $dirs
        );
    }
}
