<?php

namespace Grzojda\AlconyAi\Models;

use YoutubeDl\Exception\NoUrlProvidedException;
use YoutubeDl\Exception\YoutubeDlException;
use YoutubeDl\Options;
use YoutubeDl\Process\DefaultProcessBuilder;
use YoutubeDl\Process\ProcessBuilderInterface;
use YoutubeDl\YoutubeDl;

class YoutubeDlExtender extends YoutubeDl
{
    private ?ProcessBuilderInterface $processBuilder;
    private ?string $binPath = null;
    private ?string $pythonPath = null;

    public function __construct(ProcessBuilderInterface $processBuilder = null, \YoutubeDl\Metadata\MetadataReaderInterface $metadataReader = null, \Symfony\Component\Filesystem\Filesystem $filesystem = null)
    {
        $this->processBuilder = $processBuilder ?? new DefaultProcessBuilder();
        parent::__construct($processBuilder, $metadataReader, $filesystem);
    }

    public function getAudioUrl(Options $options): string
    {
        $arguments = [
            '-g',
            '--format=bestaudio'
        ];
        $urls = $options->getUrl();
        foreach ($urls as $url) {
            $arguments[] = $url;
        }

        if (count($urls) === 0) {
            throw new NoUrlProvidedException('Missing configured URL to download.');
        }

        $audioUrls = [];
        $process = $this->processBuilder->build($this->binPath, $this->pythonPath, $arguments);
        $process->run(function (string $type, string $buffer) use (&$audioUrls): void {
            if (str_starts_with($buffer, 'ERROR:')) {
                throw new YoutubeDlException(trim(substr($buffer, 6)));
            } elseif (str_contains($buffer, 'http')) {
                $audioUrls[] = $buffer;
            }
        });

        return $audioUrls[0];
    }

    public function setBinPath(?string $binPath): self
    {
        $this->binPath = $binPath;

        return $this;
    }

    public function setPythonPath(?string $pythonPath): self
    {
        $this->pythonPath = $pythonPath;

        return $this;
    }
}