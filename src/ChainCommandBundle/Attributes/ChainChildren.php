<?php

namespace App\ChainCommandBundle\Attributes;

use Attribute;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[Attribute(Attribute::TARGET_CLASS)]
class ChainChildren extends AutoconfigureTag
{
    public function __construct(string $parentCommand, int $sortIndex = 0)
    {
        parent::__construct(
            'app.console.chain',
            [
                'parent' => $parentCommand,
                'sortIndex' => $sortIndex
            ]
        );
    }
}
