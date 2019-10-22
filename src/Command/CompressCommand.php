<?php
/**
 * Copyright (c) Cristiano Cinotti <cristianocinotti@gmail.com>.
 *
 *  This file is part of siad-pdf-compressor package.
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *  @license GPL
 */

namespace cristianoc72\PdfCompressor\Command;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompressCommand extends BaseCommand
{
    protected static $defaultName = 'compress';

    protected function configure()
    {
        $this
            ->setDescription("Compress all the PDF into the given directory")
            ->addOption('dir', null, InputArgument::REQUIRED, 'The directory containing the files to compress.')
            ->addOption('config', null, InputArgument::REQUIRED, 'The configuration file (default is `.env`')
        ;

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {


    }

}