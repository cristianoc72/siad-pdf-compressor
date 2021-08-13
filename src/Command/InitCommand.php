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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class InitCommand extends Command
{
    protected static $defaultName = 'init';
    private Configuration $configuration;

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
        $output->writeln(
            "
<info>Initialize the application and save the configuration on an editable `.env` file</info>
"
        );

        $helper = $this->getHelper('question');

        do {
            $docDirQuestion = new Question('Please enter the name of the directory containing the documents to compress');
            $this->configuration->setDocsDir($helper->ask($input, $output, $docDirQuestion));
            $dir = new Directory($this->configuration->getDocsDir());
            if (!$dir->exists()) {
                $output->writeln("<error>Error! The document directory does not exists.</error>");
            }
        } while (!$dir->exists());

        $publicKeyQuestion = new Question(
            'Please enter the iLovePdf public key (you can leave this blank and manually insert it when running `compress` command)',
            ''
        );
        $this->configuration->setPublicKey($helper->ask($input, $output, $publicKeyQuestion));

        $privateKeyQuestion = new Question(
            'Please enter the iLovePdf private key (you can leave this blank and manually insert it when running `compress` command)',
            ''
        );
        $this->configuration->setPrivateKey($helper->ask($input, $output, $privateKeyQuestion));

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
}
