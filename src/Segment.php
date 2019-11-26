<?php

namespace Xepo;

class Segment
{
    protected $name;
    protected $description;
    protected $expression;
    protected $repos = [];
    public static function create(string $name, array $config)
    {
        $obj = new self();
        $obj->name = $name;
        $obj->description = $config['description'] ?? null;
        $obj->expression = $config['expression'] ?? null;
        return $obj;
    }

    public function setRepos(array $repos): self
    {
        $this->repos = $repos;
        return $this;
    }

    public function getRepos(): array
    {
        return $this->repos;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getExpression(): string
    {
        return $this->expression;
    }
}
