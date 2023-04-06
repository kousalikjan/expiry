<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\AppExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('notificationsCount', [AppExtensionRuntime::class, 'notificationsCount']),
            new TwigFunction('categoriesCount', [AppExtensionRuntime::class, 'categoriesCount']),
            new TwigFunction('findItemImageId', [AppExtensionRuntime::class, 'findItemImageId']),
        ];
    }
}
