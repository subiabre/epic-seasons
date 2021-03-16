<?php

namespace App\Command;

use App\Service\EpicService;
use DateTime;
use DateTimeZone;
use GifCreator\GifCreator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateGifCommand extends Command
{
    protected function configure()
    {
        $this->setName('epic:gif');
        $this->setDescription('Generate a gif with the pictures for a timezone during a year');

        $this->addArgument('timezone', InputArgument::REQUIRED, 'Name of the timezone to look for');
        $this->addArgument('start', InputArgument::REQUIRED, 'Start date to look for pictures. YYYY-MM-DD');
        $this->addArgument('end', InputArgument::REQUIRED, 'End date to look for pictures. YYYY-MM-DD');

        $this->addOption('timezone-margin', null, InputOption::VALUE_OPTIONAL, 'Amount of allowed timezone deviation margin', 5);
        $this->addOption('sourcetype', null, InputOption::VALUE_OPTIONAL, 'Type of images to use as gif frames', 'jpg');
        $this->addOption('frame-duration', null, InputOption::VALUE_OPTIONAL, 'Duration of each frame in the gif', 20);
        $this->addOption('loopings', null, InputOption::VALUE_OPTIONAL, 'Number of times to loop the gif before stopping', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $epic = new EpicService();
        $generator = new GifCreator();

        $dateStart = DateTime::createFromFormat('Y-m-d', $input->getArgument('start'));
        $dateEnd = DateTime::createFromFormat('Y-m-d', $input->getArgument('end'));
        $timezone = new DateTimeZone($input->getArgument('timezone'));
        $filename = sprintf('%s_%s_%s.gif',
            str_replace('/', '-', $input->getArgument('timezone')), 
            $input->getArgument('start'),
            $input->getArgument('end')
        );

        $output->writeln("<comment>Fetching data for the specified dates...</comment>");
        
        $data = $epic->getDataByDates($dateStart, $dateEnd);
        $dataCount = count($data);

        $output->writeln("<info>Got $dataCount elements.</info>");

        $output->writeln("<comment>Filtering images for the specified timezone...</comment>");
        
        $data = $epic->filterDataByTimezone($data, $timezone, $input->getOption('timezone-margin'));
        $dataCount = count($data);

        $output->writeln("<info>Got $dataCount elements.</info>");
        
        $frames = []; $durations = [];
        foreach ($data as $key => $value) {
            $frames[] = $epic->getImageFromData($value, $input->getOption('sourcetype'));
            $durations[] = $input->getOption('frame-duration');
        }

        $output->writeln("<comment>Generating final image...<comment>");
        $generator->create($frames, $durations, $input->getOption('loopings'));
        file_put_contents('cgi/' . $filename, $generator->getGif());

        $output->writeln("<info>Generated $filename with $dataCount frames.</info>");
        return self::SUCCESS;
    }
}
