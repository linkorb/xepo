<?php

namespace Xepo;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use RuntimeException;

class Xepo
{
    private $repoPath;
    private $configPath;
    private $excludes;
    protected $repos;

    public static function create()
    {
        $configFilename = getenv("HOME") . '/xepo.yaml';
        $yaml = file_get_contents($configFilename);
        $config = Yaml::parse($yaml);

        $xepo = new self();
        $xepo->repoPath = $config['repoPath'];
        $xepo->configPath = $config['configPath'];
        $xepo->excludes = $config['excludes'] ?? [];
        $xepo->validate();
        $xepo->initRepos();
        $xepo->initSegments();
        return $xepo;
    }


    // Perform sanity checks on configuration
    public function validate()
    {
        if (!file_exists($this->repoPath)) {
            throw new RuntimeException("Repo path not found: " . $this->repoPath);
        }
        if (!file_exists($this->configPath)) {
            throw new RuntimeException("Config path not found: " . $this->configPath);
        }
    }



    protected function initRepos()
    {
        $scanner = new Scanner();
        $repos = $scanner->scan($this->repoPath, $this->excludes);

        // Remove any repos that match `excludes` rules
        foreach ($repos as $key=>$repo) {
            foreach ($this->excludes as $exclude) {
                if (fnmatch($exclude, $repo->getFullName())) {
                    unset($repos[$key]);
                }
            }
        }

        $this->repos = $repos;
        usort($this->repos, function ($a, $b) {
            return $a->getFullName() > $b->getFullName();
        });

        foreach ($this->repos as $repo) {
            $config = [
                'fullName' => $repo->getFullName(),
                'name' => $repo->getName(),
                'ownerName' => $repo->getOwnerName(),
                'path' => $repo->getPath(),
            ];

            $filename = $repo->getPath() . '/repo.yaml';
            if (file_exists($filename)) {
                $yaml = file_get_contents($filename);
                $data = Yaml::parse($yaml);
                $config = array_merge_recursive($config, $data);
            }

            $filename = $this->getConfigPath() . '/repo/' . $repo->getFullname() . '/repo.yaml';
            if (file_exists($filename)) {
                $yaml = file_get_contents($filename);
                $data = Yaml::parse($yaml);
                $config = array_merge_recursive($config, $data);
            }

            $repo->setConfig($config);
        }
    }

    protected $segments = [];

    protected function initSegments()
    {
        $expressionLanguage = new ExpressionLanguage();
        foreach (glob($this->getConfigPath() . '/segments/*.yaml') as $filename) {
            $name = str_replace('.yaml', '', basename($filename));
            $yaml = file_get_contents($filename);
            $config = Yaml::parse($yaml);
            $segment = Segment::create($name, $config);

            $res = [];
            foreach ($this->repos as $repo) {
                $config = $repo->getConfig();
                $config = json_decode(json_encode($config)); // force to object
                $data = [
                    'repo' => $config
                ];
                // print_r($data);
                $expression = $segment->getExpression();
                // echo $expression .  PHP_EOL;
                if (@$expressionLanguage->evaluate($expression, $data)) {
                    // echo "MATCH" . PHP_EOL;
                    $res[$repo->getFullname()] = $repo;
                } else {
                    // echo "NOP" . PHP_EOL;
                }
            }
            $segment->setRepos($res);
            $this->segments[$segment->getName()] = $segment;
        }
    }

    public function getSegments(): array
    {
        return $this->segments;
    }

    public function getSegment(string $name): Segment
    {
        if (!isset($this->segments[$name])) {
            throw new RuntimeException("Undefined segment: " . $name);
        }
        return $this->segments[$name];
    }

    public function getRepos(?string $filter): array
    {
        if ($filter) {
            $segment = $this->getSegment($filter);
            return $segment->getRepos();
        }
        return $this->repos;
    }

    public function getRepo(string $name): Repo
    {
        foreach ($this->repos as $repo) {
            if ($repo->getFullName()==$name) {
                return $repo;
            }
        }
        throw new RuntimeException("No such repo: " . $name);
    }

    public function getRepoPath(): string
    {
        return $this->repoPath;
    }

    public function getConfigPath(): string
    {
        return $this->configPath;
    }

}
