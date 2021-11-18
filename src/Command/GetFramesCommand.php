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
use Symfony\Component\Console\Style\SymfonyStyle;

class GetFramesCommand extends Command
{
    public const MESSAGE_AVAILABLE = "Getting available images between %s and %s.\n";
    public const MESSAGE_FILTERING = "Filtering images for the Timezone %s.\n";
    public const MESSAGE_DOWNLOADING = "Downloading images.\n";
    public const MESSAGE_IMAGES = "Got %d images.\n";

    protected function configure()
    {
        $this->setName('frames:get');
        $this->setDescription('Get the frames for a timezone in a time period.');

        $this->addArgument('timezone', InputArgument::REQUIRED, 'Name of the timezone to look for');
        $this->addArgument('start', InputArgument::REQUIRED, 'Start date to look for pictures. YYYY-MM-DD');
        $this->addArgument('end', InputArgument::REQUIRED, 'End date to look for pictures. YYYY-MM-DD');

        $this->addOption('margin', null, InputOption::VALUE_OPTIONAL, 'Amount of allowed timezone deviation margin', 5);
        $this->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Type of images to get', 'jpg');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $epic = new EpicService();

        $directory = sprintf('%s/var/frames', dirname(__DIR__, 2));

        $timezone = new DateTimeZone($input->getArgument('timezone'));

        $dateStart = DateTime::createFromFormat('Y-m-d', $input->getArgument('start'));
        $dateEnd = DateTime::createFromFormat('Y-m-d', $input->getArgument('end'));

        $margin = $input->getOption('margin');
        $type = $input->getOption('type');
        
        $io->write(sprintf(self::MESSAGE_AVAILABLE, $dateStart->format('Y-m-d'), $dateEnd->format('Y-m-d')));
        $available = $epic->getDataByDates($dateStart, $dateEnd);
        $io->write(sprintf(self::MESSAGE_IMAGES, count($available)));

        $io->write(sprintf(self::MESSAGE_FILTERING, $timezone->getName()));
        $filtered = $epic->filterDataByTimezone($available, $timezone, $margin);
        $io->write(sprintf(self::MESSAGE_IMAGES, count($filtered)));

        if (!file_exists($directory)) {
            mkdir($directory);
        }

        $io->write(self::MESSAGE_DOWNLOADING);
        $io->progressStart(count($filtered));
        foreach ($filtered as $key => $value) {
            copy(
                $epic->getImageFromData($value, $type),
                sprintf('%s/epic_%s.%s', $directory, $key, $type)
            );

            $io->progressAdvance();
        }
        $io->progressFinish();

        return self::SUCCESS;
    }
}
