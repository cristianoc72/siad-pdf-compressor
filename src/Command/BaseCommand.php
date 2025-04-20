<?php declare(strict_types=1);
/*
 * Copyright (c) 2021 - 2025 Cristiano Cinotti.
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
            ->addOption('docs-dir', null, InputArgument::REQUIRED, 'The directory containing the pdf files to compress.')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $options = ['docs-dir', 'private-key', 'public-key', 'log-file'];

        foreach ($options as $option) {
            if ($input->hasOption($option) && !empty($input->getOption($option))) {
                $this->configuration->set(str_replace('-', '_', $option), $input->getOption($option));
            }
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
