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
use phootwork\file\Directory;
use phootwork\file\exception\FileException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(name: 'init')]
class InitCommand extends Command
{
    private Configuration $configuration;
    private ?QuestionHelper $helper = null;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription("Initialize the application and save the options in a `.env` file")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var QuestionHelper helper */
        $this->helper = $this->getHelper('question');

        $output->writeln(
            "
<info>Initialize the application and save the configuration on an editable `.env` file</info>
"
        );

        $this->populateDirectory($input, $output);
        $this->populatePublicKey($input, $output);
        $this->populatePrivateKey($input, $output);
        $this->populateLogFile($input, $output);

        try {
            $this->configuration->saveConfiguration();
        } catch (FileException $e) {
            $output->writeln("<error>Error! {$e->getMessage()}</error>");

            return Command::FAILURE;
        }
        $output->writeln('Configuration file successfully created!');
        $output->writeln('If you want to change it, please run `init` command again.');

        return Command::SUCCESS;
    }

    private function populateDirectory(InputInterface $input, OutputInterface $output): void
    {
        do {
            $docDirQuestion = new Question(
                'Please enter the name of the directory containing the documents to compress',
                'C:\\siad'
            );
            $this->configuration->setDocsDir((string) $this->helper->ask($input, $output, $docDirQuestion));
            $dir = new Directory($this->configuration->getDocsDir());
            if (!$dir->exists()) {
                $output->writeln("<error>Error! The document directory does not exists.</error>");
            }
        } while (!$dir->exists());
    }

    private function populatePublicKey(InputInterface $input, OutputInterface $output): void
    {
        $publicKeyQuestion = new Question(
            'Please enter the iLovePdf public key (you can leave this blank and manually insert it when running `compress` command)',
            ''
        );
        $this->configuration->setPublicKey((string) $this->helper->ask($input, $output, $publicKeyQuestion));
    }

    private function populatePrivateKey(InputInterface $input, OutputInterface $output): void
    {
        $privateKeyQuestion = new Question(
            'Please enter the iLovePdf private key (you can leave this blank and manually insert it when running `compress` command)',
            ''
        );
        $this->configuration->setPrivateKey((string) $this->helper->ask($input, $output, $privateKeyQuestion));
    }

    private function populateLogFile(InputInterface $input, OutputInterface $output): void
    {
        $logFileQuestion = new Question(
            'Please enter the path for your log file',
            $this->configuration->getDocsDir() . '/pdf-compressor.log'
        );
        $this->configuration->setLogFile((string) $this->helper->ask($input, $output, $logFileQuestion));
    }
}
