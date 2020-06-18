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

use Symfony\Component\Finder\Finder;

/**
 * Class Compiler
 *
 * Compiles the library into a Phar archive.
 *
 * @package cristianoc72\PdfCompressor
 */
class Compiler
{
    /**
     * Compile the utility into a phar archive.
     * Before running this, remove the --dev dependencies
     * to avoid to include them into the archive: `composer install --no-dev`
     *
     * @param string $pharFile the name of the phar archive
     */
    public static function compile(string $pharFile = 'compressor.phar'): void
    {
        $phar = new \Phar($pharFile, 0, 'compressor.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->name('LICENSE')
            ->notName('Compiler.php')
            ->exclude('Tests')
            ->exclude('tests')
            ->exclude('docs')
            ->exclude('samples')
            ->exclude('vendor/cypresslab')
            ->in(__DIR__.'/..')
        ;

        $phar->buildFromIterator($finder, __DIR__ . '/..');
        $phar->setDefaultStub('bin/compressor.php');
    }

    private static function createBin(): void
    {
        $stub = <<<STUB
#!/usr/bin/php
<?php declare(strict_types=1);

use cristianoc72\PdfCompressor\Container;

require 'vendor/autoload.php';

putenv("VERSION={{version}}");

\$container = new Container();
\$app = \$container->get('app');
\$app->run();

STUB;

        
    }
}
