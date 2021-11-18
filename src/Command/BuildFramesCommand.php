<?php

namespace App\Command;

use SplFileObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BuildFramesCommand extends Command
{
    public const MESSAGE_MISSING_FRAMES = "The frames folder is empty.";

    protected function configure()
    {
        $this->setName('frames:build');
        $this->setDescription('Build a video with the available frames.');

        $this->addArgument('filename', InputArgument::REQUIRED, 'Name of the final video file');
        $this->addArgument('framerate', InputArgument::OPTIONAL, 'Number of frames per second', 24);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $filename = $input->getArgument('filename');
        $framerate = $input->getArgument('framerate');
        $extension = $this->getExtension();

        if (!$extension) {
            $io->error(self::MESSAGE_MISSING_FRAMES);

            return self::FAILURE;
        }

        echo exec("ffmpeg -framerate $framerate -i 'var/frames/epic_%d.$extension' $filename.mp4");

        return self::SUCCESS;
    }

    private function getExtension()
    {
        $directory = sprintf('%s/var/frames/', dirname(__DIR__, 2));
        $frames = scandir($directory);
        
        if (count($frames) < 3) return null;

        return (new SplFileObject(sprintf('%s%s', $directory, $frames[2])))->getExtension();
    }
}
