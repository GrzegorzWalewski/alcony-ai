<?php
//
//declare(strict_types=1);
//
//include __DIR__.'/vendor/autoload.php';
////
////use YoutubeDl\Options;
////use YoutubeDl\YoutubeDl;
////
////$yt = new YoutubeDl();
////
////$a = $yt->download(
////    Options::create()
////        ->downloadPath('/path/to/downloads')
////        ->extractAudio(true)
////        ->audioFormat('mp3')
////        ->audioQuality('0') // best
////        ->output('%(title)s.%(ext)s')
////        ->url('https://www.youtube.com/watch?v=oDAw7vW7H0c')
////);
////
//
////
////var_dump($a);
//
//use Symfony\Component\Process\ExecutableFinder;
//use YoutubeDl\Exception\ExecutableNotFoundException;
//use YoutubeDl\Exception\NoUrlProvidedException;
//use YoutubeDl\Exception\YoutubeDlException;
//use YoutubeDl\Options;
//use YoutubeDl\Process\ArgvBuilder;
//use YoutubeDl\Process\DefaultProcessBuilder;
//use YoutubeDl\YoutubeDl;
//
//
//use Symfony\Component\Process\Process;
//use YoutubeDl\Process\ProcessBuilderInterface;
//
//class YoutubeLinkExtractor implements ProcessBuilderInterface
//{
//    const CUSTOM_ARGUMENTS = [
//        '--ignore-config',
//        '--ignore-errors',
//        '--write-info-json',
//        '-g',
//        '--format=bestaudio'
//    ];
//
//    private DefaultProcessBuilder $defaultProcessorBuilder;
//
//    public function __construct(DefaultProcessBuilder $defaultProcessBuilder)
//    {
//        $this->defaultProcessorBuilder = $defaultProcessBuilder;
//    }
//
//    public function build(?string $binPath, ?string $pythonPath, array $arguments = []): Process
//    {
//        var_dump($arguments);
//        $arguments = self::CUSTOM_ARGUMENTS;
//
//        return $this->defaultProcessorBuilder->build($binPath, $pythonPath, $arguments);
//    }
//}
//
//class YoutubeDlExtender extends YoutubeDl
//{
//    private ?ProcessBuilderInterface $processBuilder;
//    private ?string $binPath = null;
//    private ?string $pythonPath = null;
//
//    public function __construct(ProcessBuilderInterface $processBuilder = null, \YoutubeDl\Metadata\MetadataReaderInterface $metadataReader = null, \Symfony\Component\Filesystem\Filesystem $filesystem = null)
//    {
//        $this->processBuilder = $processBuilder ?? new DefaultProcessBuilder();
//        parent::__construct($processBuilder, $metadataReader, $filesystem);
//    }
//
//    public function getAudioUrl(Options $options): array
//    {
//        $arguments = [
//            '-g',
//            '--format=bestaudio'
//        ];
//        $urls = $options->getUrl();
//        foreach ($urls as $url) {
//            $arguments[] = $url;
//        }
//
//        if (count($urls) === 0) {
//            throw new NoUrlProvidedException('Missing configured URL to download.');
//        }
//
//        $audioUrls = [];
//
//        $process = $this->processBuilder->build($this->binPath, $this->pythonPath, $arguments);
//        $process->run(function (string $type, string $buffer) use (&$audioUrls): void {
//            if (str_starts_with($buffer, 'ERROR:')) {
//                throw new YoutubeDlException(trim(substr($buffer, 6)));
//            } elseif (str_contains($buffer, 'http')) {
//                $audioUrls[] = $buffer;
//            }
//        });
//
//        return $audioUrls;
//    }
//
//    public function setBinPath(?string $binPath): self
//    {
//        $this->binPath = $binPath;
//
//        return $this;
//    }
//
//    public function setPythonPath(?string $pythonPath): self
//    {
//        $this->pythonPath = $pythonPath;
//
//        return $this;
//    }
//}
//
//$youtube = new YoutubeDlExtender();
//$youtube->setPythonPath('/usr/bin/python3');
//$audioUrls = $youtube->getAudioUrl(
//    Options::create()
//        ->url('https://www.youtube.com/watch?v=oDAw7vW7H0c')
//);
//
//var_dump($audioUrls[0]);
//
////
////$yt = new YoutubeDl(new YoutubeLinkExtractor(new DefaultProcessBuilder()));
////$yt->setPythonPath('/usr/bin/python3');
////$collection = $yt->download(
////    Options::create()
////        ->downloadPath(__DIR__ . '/ytTemp')
////        ->url('https://www.youtube.com/watch?v=oDAw7vW7H0c')
////);
////
////var_dump($collection);
////
////foreach ($collection->getVideos() as $video) {
////    if ($video->getError() !== null) {
////        echo "Error downloading video: {$video->getError()}.";
////    } else {
////        var_dump($video->getFile()); // audio file
////    }
////}
