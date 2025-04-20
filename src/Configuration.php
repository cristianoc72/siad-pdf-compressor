<?php declare(strict_types=1);
/*
 * Copyright (c) 2021 - 2025 Cristiano Cinotti
 *
 * This file is part of siad-pdf-compressor package, release under the APACHE-2 license.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cristianoc72\PdfCompressor;

use Dflydev\DotAccessData\Data;
use phootwork\file\exception\FileException;
use phootwork\file\File;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Configuration.
 * Class to manage the configuration values.
 */
class Configuration extends Data
{
    public const DEFAULT_DIR = "C:\\siad";

    /**
     * @param ?string $path The path where to find the configuration file
     * @throws ParseException If errors occur while parsing the configuration file.
     */
    public function __construct(?string $path = null)
    {
        $fileName = $this->getConfigFileName($path);
        $configArray = Yaml::parseFile($fileName);
        parent::__construct($configArray);
        $this->set('file_name', $fileName);
    }

    /**
     * Build and save the configuration file.
     *
     * @throws FileException If something went wrong in writing configuration file.
     */
    public function saveConfiguration(): void
    {
        $file = new File($this->get('file_name'));
        if (!$file->isWritable()) {
            throw new FileException("Impossible to write the file `{$this->get('file_name')}`: do you have the correct permissions?");
        }

        $array = $this->export();
        unset($array['file_name']);
        $file->write(Yaml::dump($array));
    }

    /**
     * Return the configuration file name.
     *
     * @throws FileException If more then one configuration file is found.
     */
    private function getConfigFileName(?string $path = null): string
    {
        $path = $path ?? ($_SERVER['HOME'] ?? self::DEFAULT_DIR);
        $finder = new Finder();
        $finder->in($path)->name('siad-pdf-compressor.yaml')->name('siad-pdf-compressor.yml')->files();

        if ($finder->count() !== 1) {
            throw new FileException("There must be one configuration file: {$finder->count()} found.");
        }

        $fileName = '';
        foreach ($finder as $file) {
            $fileName = $file->getPathname();
        }

        return $fileName;
    }
}
