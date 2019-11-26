<?php

namespace Xepo\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Xepo\Xepo;
use RuntimeException;

class RepoListCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('repo:list')
            ->setDescription('Lists repositories')
            ->addArgument(
                'segment',
                InputArgument::OPTIONAL,
                'Segment name'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $xepo = Xepo::create();
        $segmentName = $input->getArgument('segment');

        $repos = $xepo->getRepos($segmentName);

        foreach ($repos as $repo) {
            $output->writeLn("<comment>" . $repo->getOwnerName()  . "</comment>/<info>" . $repo->getName() . '</info> ' . $repo->getPath());
        }
        $output->writeLn("Total: " . count($repos));
    }
}
