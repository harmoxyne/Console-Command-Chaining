<?php

namespace App;

use App\ChainCommandBundle\DependencyInjection\CommandChainCompilerPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function build(ContainerBuilder $container): void
    {
        if ($this->getEnvironment() === 'test') {
//            $container->addCompilerPass(new TestContainerPass());
        }

        $container->addCompilerPass(new CommandChainCompilerPass());
    }
}
