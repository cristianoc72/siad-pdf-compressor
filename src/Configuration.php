<?php declare(strict_types=1);
/*
 * Copyright (c) Cristiano Cinotti 2021.
 *
 * This file is part of siad-pdf-compressor package, release under the APACHE-2 license.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cristianoc72\PdfCompressor;

use phootwork\file\exception\FileException;
use phootwork\file\File;
use Symfony\Component\Dotenv\Dotenv;

class Configuration
{
    private string $docsDir;
    private string $publicKey = '';
    private string $privateKey = '';
    private string $fileName = '';
    private string $logFile = '';

    public function __construct(string $path = null)
    {
        $path = $path ?? $_SERVER['HOME'];
        $this->fileName = "$path/.env";

        $dotEnv = new Dotenv();
        $dotEnv->load($this->fileName);

        $this->docsDir = $_ENV['DOCS_DIR'];
        $this->privateKey = $_ENV['PRIVATE_KEY'];
        $this->publicKey = $_ENV['PUBLIC_KEY'];
        $this->logFile = $_ENV['LOG_FILE'];
    }

    public function getDocsDir(): string
    {
        return $this->docsDir;
    }

    public function setDocsDir(string $docsDir): void
    {
        $this->docsDir = $docsDir;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    public function setPrivateKey(string $privateKey): void
    {
        $this->privateKey = $privateKey;
    }

    public function getLogFile(): string
    {
        return $this->logFile;
    }

    public function setLogFile(string $logFile): void
    {
        $this->logFile = $logFile;
    }

    /**
     * @throws FileException If something went wrong in reading template and writing `.env` file
     */
    public function saveConfiguration(): void
    {
        $tplFile = new File(__DIR__ . '/../resources/templates/.env.mustache');
        $content = $tplFile->read()
            ->replace(
                ['{{ docsDir }}', '{{ privateKey }}', '{{ publicKey }}', '{{ logFile }}'],
                [$this->docsDir, $this->privateKey, $this->publicKey, $this->logFile]
            );
        $file = new File($this->fileName);
        if (!$file->isWritable()) {
            throw new FileException("Impossible to write the file `{$this->fileName}`: do you have the correct permissions?");
        }
        $file->write($content);
    }
}
