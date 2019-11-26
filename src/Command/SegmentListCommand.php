<?php

namespace Xepo\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Xepo\Xepo;
use RuntimeException;

class SegmentListCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('segment:list')
            ->setDescription('Lists segments')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $xepo = Xepo::create();

        $segments = $xepo->getSegments();

        foreach ($segments as $segment) {
            $output->writeLn("<info>" . $segment->getName()  . "</info> " . $segment->getDescription());
        }
        $output->writeLn("Total: " . count($segments));
    }
}
