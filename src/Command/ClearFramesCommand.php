<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearFramesCommand extends Command
{
    protected function configure()
    {
        $this->setName('frames:clear');
        $this->setDescription('Clear the available downloaded frames.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo exec(sprintf('rm -rf %s/var/frames', dirname(__DIR__, 2)));

        return self::SUCCESS;
    }
}
