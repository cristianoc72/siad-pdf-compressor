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
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class BaseCommand extends Command
{
    protected Finder $finder;
    protected Logger $logger;
    protected Configuration $configuration;
    protected bool $errors = false;

    public function __construct(Finder $finder, Logger $logger, Configuration $configuration)
    {
        $this->finder = $finder;
        $this->logger = $logger;
        $this->configuration = $configuration;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dir', null, InputArgument::REQUIRED, 'The directory containing the pdf files to compress.')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if ($input->hasOption('dir') && !empty($input->getOption('dir'))) {
            $this->configuration->setDocsDir($input->getOption('dir'));
        }
        if ($input->hasOption('public-key') && !empty($input->getOption('public-key'))) {
            $this->configuration->setPublicKey($input->getOption('public-key'));
        }
        if ($input->hasOption('private-key') && !empty($input->getOption('private-key'))) {
            $this->configuration->setPrivateKey($input->getOption('private-key'));
        }
        if ($input->hasOption('log-file') && !empty($input->getOption('log-file'))) {
            $this->configuration->setLogFile($input->getOption('log-file'));
        }
    }

    protected function showError(\Exception $exception, OutputInterface $output): void
    {
        $message = get_class($exception) . ": {$exception->getMessage()}";
        $this->logger->error($message);
        $output->writeln("<error>$message</error>");
        $this->errors = true;
    }
}
