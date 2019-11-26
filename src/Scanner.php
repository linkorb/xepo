<?php

namespace Xepo;

use Xepo;

class Scanner
{
    public function scan(string $path): array
    {
        $paths = [];
        $this->scanRecursive($path, $paths);

        $repos = [];
        foreach ($paths as $path) {
            $repo = new Repo();
            $repo->setName(basename($path));
            $repo->setOwnerName(basename(dirname($path)));

            $repo->setPath($path);
            $repos[$repo->getFullName()] = $repo;
        }
        return $repos;
    }

    private function scanRecursive($path, &$paths): void
    {
        $files = scandir($path);
        foreach ($files as $filename) {
            $skip = false;
            switch ($filename) {
                case '.':
                case '..':
                case '.git':
                case 'vendor':
                case 'node_modules':
                    $skip = true;
                    break;
            }

            if (!$skip) {
                if (is_dir($path . '/' . $filename)) {
                    if (file_exists($path . '/' . $filename . '/.git/HEAD')) {
                        // Found a .git repository, add it to the dirs list
                        $paths[] = $path . '/' . $filename;
                    } else {
                        $this->scanRecursive($path . '/' . $filename, $paths);
                    }
                }
            }
        }
    }
}
