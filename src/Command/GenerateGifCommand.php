<?php

namespace App\Command;

use App\Service\EpicService;
use AppGati;
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
        $gati = new AppGati();
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
        $gati->step('fetching');

        $data = $epic->getDataByDates($dateStart, $dateEnd);
        $gati->step('fetched');

        $dataCount = count($data);
        $timeSpent = $gati->getTimeDifference('fetching', 'fetched');

        $output->writeln("<info>Got $dataCount elements in $timeSpent seconds.</info>");

        $output->writeln("<comment>Filtering images for the specified timezone...</comment>");
        $gati->step('filtering');
        
        $data = $epic->filterDataByTimezone($data, $timezone, $input->getOption('timezone-margin'));
        $durations = array_fill(0, count($data), $input->getOption('frame-duration'));

        $frames = []; 
        foreach ($data as $key => $value) {
            $frames[] = $epic->getImageFromData($value, $input->getOption('sourcetype'));
        }

        $gati->step('filtered');

        $dataCount = count($data);
        $timeSpent = $gati->getTimeDifference('filtering', 'filtered');

        $output->writeln("<info>Got $dataCount elements in $timeSpent seconds.</info>");

        $output->writeln("<comment>Generating final image...<comment>");
        $gati->step('generating');

        $generator->create($frames, $durations, $input->getOption('loopings'));
        file_put_contents('cgi/' . $filename, $generator->getGif());

        $gati->step('generated');

        $timeSpent = $gati->getTimeDifference('generating', 'generated');

        $output->writeln("<info>Generated $filename with $dataCount frames in $timeSpent.</info>");
        return self::SUCCESS;
    }
}
