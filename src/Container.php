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
use cristianoc72\PdfCompressor\Command\InitCommand;
use cristianoc72\PdfCompressor\Command\RevertCommand;
use Exception;
use Ilovepdf\CompressTask;
use Ilovepdf\Ilovepdf;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;

class Container extends ContainerBuilder
{
    public function __construct(string $home = null, ParameterBagInterface $parameterBag = null)
    {
        parent::__construct($parameterBag);

        $home = $home ?? $_SERVER['HOME'];

        // Services
        $this->addConfiguration($home);
        $this->register('finder', Finder::class);
        $this->addIlovePdf();
        $this->addLogger();
        $this->addCommands();
        $this->addApplication();
    }

    private function addConfiguration(string $home): void
    {
        $this->register('configuration', Configuration::class)
            ->addArgument($home);
    }

    private function addIlovePdf(): void
    {
        /** @var Configuration $config */
        $config = $this->get('configuration');
        $this->register('iLovePdf', Ilovepdf::class)
            ->addArgument($config->getPublicKey())
            ->addArgument($config->getPrivateKey())
        ;
    }

    /**
     * @throws Exception
     */
    private function addLogger(): void
    {
        $this->register('streamHandler', StreamHandler::class)
            ->addArgument($this->get('configuration')?->getLogFile())
        ;
        $this->register('logger', Logger::class)
            ->addArgument('Siad Pdf Compressor')
            ->addMethodCall('pushHandler', [new Reference('streamHandler')]);
    }

    private function addCommands(): void
    {
        $this->register('compress', CompressCommand::class)
            ->addArgument(new Reference('finder'))
            ->addArgument(new Reference('logger'))
            ->addArgument(new Reference('configuration'))
            ->addArgument(new Reference('iLovePdf'))
        ;

        $this->register('revert', RevertCommand::class)
            ->addArgument(new Reference('finder'))
            ->addArgument(new Reference('logger'))
            ->addArgument(new Reference('configuration'))
        ;

        $this->register('init', InitCommand::class)
            ->addArgument(new Reference('configuration'))
        ;
    }

    private function addApplication(): void
    {
        $this->register('app', Application::class)
            ->addArgument('Siad Pdf Compressor')
            ->addMethodCall('addCommands', [
                [new Reference('compress'), new Reference('revert'), new Reference('init')]
            ])
        ;
    }
}
