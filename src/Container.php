<?php declare(strict_types=1);
/**
 * Copyright (c) 2020 Cristiano Cinotti
 *
 * This file is part of siad-pdf-compressor package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license Apache-2.0
 */

namespace cristianoc72\PdfCompressor;


use cristianoc72\PdfCompressor\Command\CompressCommand;
use Ilovepdf\CompressTask;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Finder\Finder;

class Container extends ContainerBuilder
{
    public function __construct(ParameterBagInterface $parameterBag = null)
    {
        if ($parameterBag === null) {
            $parameterBag = new ParameterBag();
        }

        $parameterBag->add(['version' => getenv('VERSION'), 'home' => $_SERVER['HOME']]);
        parent::__construct($parameterBag);

        // Services
        $this->addDotEnv();
        $this->register('finder', Finder::class);
        $this->addIlovePdf();
        $this->addLogger();
        $this->addCommands();
        $this->addApplication();
    }

    private function addDotEnv(): void
    {
        $this->register('dotEnv', Dotenv::class)
            ->addMethodCall('load', ["%home%/.env"])
        ;
    }

    private function addIlovePdf(): void
    {
        $this->register('iLovePdf', CompressTask::class)
            ->addArgument("%env(PUBLIC_KEY)%")
            ->addArgument("%env(PRIVATE_KEY)%")
        ;
    }

    private function addLogger(): void
    {
        $this->register('streamHandler', StreamHandler::class)
            ->addArgument("%env(DOCS_DIR)%/pdf-compressor.log")
        ;
        $this->register('logger', Logger::class)
            ->addArgument('Siad Pdf Compressor')
            ->addMethodCall('pushHandler', [new Reference('streamHandler')]);
    }

    private function addCommands(): void
    {
        $this->register('compress', CompressCommand::class)
            ->addArgument(new Reference('finder'))
            ->addArgument(new Reference('iLovePdf'))
            ->addArgument(new Reference('logger'))
        ;
    }

    private function addApplication(): void
    {
        $this->register('app', Application::class)
            ->addArgument('Siad Pdf Compressor')
            ->addArgument("%version%")
            ->addMethodCall('addCommands', [
                new Reference('compress')
            ])
        ;
    }
}