<?php declare(strict_types=1);
/*
 * Copyright (c) 2021 - 2025 Cristiano Cinotti
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
use Symfony\Component\Console\Question\ConfirmationQuestion;
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
<info>Initialize the application and save the configuration on an editable `siad-pdf-compressor.yaml` file</info>
"
        );

        $this->populateDirectory($input, $output);
        $this->populatePublicKey($input, $output);
        $this->populatePrivateKey($input, $output);
        $this->populateLogFile($input, $output);
        $this->populatePreInvoice($input, $output);
        $this->populateExcludes($input, $output);

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
                'C:\\siad',
                $this->configuration->get('docs_dir')
            );
            $this->configuration->set('docs_dir', (string) $this->helper?->ask($input, $output, $docDirQuestion));
            $dir = new Directory($this->configuration->get('docs_dir'));
            if (!$dir->exists()) {
                $output->writeln("<error>Error! The document directory does not exists.</error>");
            }
        } while (!$dir->exists());
    }

    private function populatePublicKey(InputInterface $input, OutputInterface $output): void
    {
        $publicKeyQuestion = new Question(
            'Please enter the iLovePdf public key (you can leave this blank and manually insert it when running `compress` command)',
            $this->configuration->get('public_key')
        );
        $this->configuration->set('public_key', (string) $this->helper?->ask($input, $output, $publicKeyQuestion));
    }

    private function populatePrivateKey(InputInterface $input, OutputInterface $output): void
    {
        $privateKeyQuestion = new Question(
            'Please enter the iLovePdf private key (you can leave this blank and manually insert it when running `compress` command)',
            $this->configuration->get('private_key')
        );
        $this->configuration->set('private_key', (string) $this->helper?->ask($input, $output, $privateKeyQuestion));
    }

    private function populateLogFile(InputInterface $input, OutputInterface $output): void
    {
        $logFileQuestion = new Question(
            'Please enter the path for your log file',
            $this->configuration->get('docs_dir') . '/pdf-compressor.log'
        );
        $this->configuration->set('log_file', (string) $this->helper?->ask($input, $output, $logFileQuestion));
    }

    private function populatePreInvoice(InputInterface $input, OutputInterface $output): void
    {
        $preInvoiceQuestion = new ConfirmationQuestion('Do you want to disable the creation of the copies of the files, to use in pre-invoices?', false, '/^(y|s)/i');
        $this->configuration->set('disable_preinvoice', (bool) $this->helper?->ask($input, $output, $preInvoiceQuestion));
    }

    private function populateExcludes(InputInterface $input, OutputInterface $output): void
    {
        $default = $this->configuration->has('excludes') ? implode("\n", $this->configuration->get('excludes')) : null;
        $question = new Question("Please enter some directories you want to exclude from documentation searching
(subdirectories of {$this->configuration->get('docs_dir')}).
Write each directory and press enter.
Digit Ctrl-Z to end (Ctrl-D on Linux or Mac).
",
            $default
        );
        $question->setMultiline(true);
        $question->setNormalizer(fn(string $value): array => explode("\n", $value));

        $this->configuration->set('excludes', $this->helper?->ask($input, $output, $question));
    }
}
