<?php declare(strict_types=1);
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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    protected static $defaultName = 'init';

    protected function configure()
    {
        $this
            ->setDescription("Initialize the application via a `.env` file")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // ...
    }
}
