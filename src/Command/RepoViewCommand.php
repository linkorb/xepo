<?php

namespace Xepo\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;
use Xepo\Xepo;
use RuntimeException;

class RepoViewCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('repo:view')
            ->setDescription('View repo details')
            ->addArgument(
                'repo',
                InputArgument::REQUIRED,
                'Full name of the repo to obtain details from'
            )

        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $xepo = Xepo::create();
        $repoFullName = $input->getArgument('repo');

        $repo = $xepo->getRepo($repoFullName);
        $config = $repo->getConfig();
        $yaml = Yaml::dump($config, 99, 2);
        $output->writeLn($yaml);
    }
}
