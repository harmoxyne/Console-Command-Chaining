<?php

namespace App\ChainCommandBundle\ServiceLocator;

use Symfony\Component\Console\Command\Command;

class CommandChainLocator
{
    /**
     * @var array - contains cached and already sorted chains, updates each time command is added to the chain
     */
    private array $chains = [];

    private array $rawChains = [];

    public function addChainCommand(Command $command, string $parentAlias, int $sortingIndex = 0): void
    {
        if (!isset($this->rawChains[$parentAlias])) {
            $this->rawChains[$parentAlias] = [];
        }

        $this->rawChains[$parentAlias][] = [
            'command' => $command,
            'sortingIndex' => $sortingIndex
        ];

        $this->chains[$parentAlias] = $this->recalculateChain($parentAlias);
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

    private function recalculateChain(string $parentAlias): array
    {
        $sortedChain = $this->rawChains[$parentAlias];
        usort($sortedChain, function ($a, $b) {
            return $a['sortingIndex'] - $b['sortingIndex'];
        });

        $resultChain = [];
        foreach ($sortedChain as $command) {
            $resultChain[] = $command['command'];
        }

        return $resultChain;
    }
}
