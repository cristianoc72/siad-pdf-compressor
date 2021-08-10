<?php declare(strict_types=1);
/*
 * Copyright (c) Cristiano Cinotti 2021.
 *
 * This file is part of siad-pdf-compressor package, release under the APACHE-2 license.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cristianoc72\PdfCompressor;

use Symfony\Component\Dotenv\Dotenv;

class Configuration
{
    private string $docsDir;
    private string $publicKey = '';
    private string $privateKey = '';
    private Dotenv $dotEnv;

    public function __construct(string $path = null)
    {
        $path = $path ?? $_SERVER['HOME'];
        $this->dotEnv = new Dotenv();
        $this->dotEnv->load("$path/.env");

        $this->docsDir = $_ENV['DOCS_DIR'];
        $this->privateKey = $_ENV['PRIVATE_KEY'];
        $this->publicKey = $_ENV['PUBLIC_KEY'];
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

    public function getDotEnv(): Dotenv
    {
        return $this->dotEnv;
    }
}
