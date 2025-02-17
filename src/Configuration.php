<?php declare(strict_types=1);
/*
 * Copyright (c) 2021 - 2025 Cristiano Cinotti
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
    public const DEFAULT_DIR = "C:\\siad";

    private string $docsDir;
    private string $publicKey = '';
    private string $privateKey = '';
    private string $fileName = '';
    private string $logFile = '';
    private bool $disablePreInvoice = false;

    public function __construct(?string $path = null)
    {
        $path = $path ?? ($_SERVER['HOME'] ?? self::DEFAULT_DIR);
        $this->fileName = "$path/.env";

        $dotEnv = new Dotenv();
        $dotEnv->load($this->fileName);

        $this->docsDir = $_ENV['DOCS_DIR'];
        $this->privateKey = $_ENV['PRIVATE_KEY'];
        $this->publicKey = $_ENV['PUBLIC_KEY'];
        $this->logFile = $_ENV['LOG_FILE'];
        $this->disablePreInvoice = $_ENV['DISABLE_PREINVOICE'] === 'true' ? true : false;
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

    public function isDisablePreInvoice(): bool
    {
        return $this->disablePreInvoice;
    }

    public function setDisablePreInvoice(bool $value): void
    {
        $this->disablePreInvoice = $value;
    }

    /**
     * @throws FileException If something went wrong in reading template and writing `.env` file
     */
    public function saveConfiguration(): void
    {
        $tplFile = new File(__DIR__ . '/../resources/templates/.env.mustache');
        $content = $tplFile->read()
            ->replace(
                ['{{ docsDir }}', '{{ privateKey }}', '{{ publicKey }}', '{{ logFile }}', '{{ disablePreInvoice }}'],
                [$this->docsDir, $this->privateKey, $this->publicKey, $this->logFile, $this->disablePreInvoice ? 'true' : 'false']
            );
        $file = new File($this->fileName);
        if (!$file->isWritable()) {
            throw new FileException("Impossible to write the file `{$this->fileName}`: do you have the correct permissions?");
        }
        $file->write($content);
    }
}
