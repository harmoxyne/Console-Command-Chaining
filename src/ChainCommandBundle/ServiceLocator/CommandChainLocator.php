<?php

namespace App\ChainCommandBundle\ServiceLocator;

use Symfony\Component\Console\Command\Command;

class CommandChainLocator
{
    /**
     * @var Command[][]
     */
    private array $chains = [];

    public function addChainCommand(Command $command, string $parentAlias, int $sortingIndex = 0): void
    {
        if (!isset($this->chains[$parentAlias])) {
            $this->chains[$parentAlias] = [];
        }

        $this->chains[$parentAlias][] = $command;
    }

    /**
     * @param string $alias
     * @return Command[]
     */
    public function getChainCommands(string $alias): array
    {
        return $this->chains[$alias] ?? [];
    }

    public function getChainByChildren(string $alias): ?string
    {
        foreach ($this->chains as $chainName => $chain) {
            foreach ($chain as $command) {
                if ($command->getName() === $alias) {
                    return $chainName;
                }
            }
        }

        return null;
    }
}
