<?php

namespace App\ChainCommandBundle\DependencyInjection;

use App\ChainCommandBundle\ServiceLocator\CommandChainLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CommandChainCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $commandChainLocator = $container->getDefinition(CommandChainLocator::class);

        foreach ($container->findTaggedServiceIds('app.console.chain') as $id => $tags) {
            foreach ($tags as $tag) {
                $commandChainLocator->addMethodCall(
                    'addChainCommand',
                    [
                        new Reference($id),
                        $tag['parent'],
                        (int)($tag['sort_index'] ?? 0)
                    ]);
            }
        }
    }
}
