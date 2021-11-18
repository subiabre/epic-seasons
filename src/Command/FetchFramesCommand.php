<?php

namespace App\Command;

use App\Service\EpicService;
use DateTime;
use DateTimeZone;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FetchFramesCommand extends Command
{
    protected function configure()
    {
        $this->setName('epic:frames');
        $this->setDescription('Get the pictures of Earth for a timezone during a year');

        $this->addArgument('timezone', InputArgument::REQUIRED, 'Name of the timezone to look for');
        $this->addArgument('start', InputArgument::REQUIRED, 'Start date to look for pictures. YYYY-MM-DD');
        $this->addArgument('end', InputArgument::REQUIRED, 'End date to look for pictures. YYYY-MM-DD');

        $this->addOption('margin', null, InputOption::VALUE_OPTIONAL, 'Amount of allowed timezone deviation margin', 5);
        $this->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Type of images to get', 'jpg');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $epic = new EpicService();

        $timezone = new DateTimeZone($input->getArgument('timezone'));

        $dateStart = DateTime::createFromFormat('Y-m-d', $input->getArgument('start'));
        $dateEnd = DateTime::createFromFormat('Y-m-d', $input->getArgument('end'));

        $margin = $input->getOption('margin');
        $type = $input->getOption('type');
        
        $available = $epic->getDataByDates($dateStart, $dateEnd);
        $filtered = $epic->filterDataByTimezone($available, $timezone, $margin);

        foreach ($filtered as $key => $value) {
            copy(
                $epic->getImageFromData($value, $type),
                sprintf('cgi/%s.%s', $value['image'], $type)
            );
        }

        return self::SUCCESS;
    }
}
