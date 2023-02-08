<?php

namespace App\Twig\Runtime;

use App\Repository\ItemRepository;
use Twig\Extension\RuntimeExtensionInterface;

class AppExtensionRuntime implements RuntimeExtensionInterface
{

    private ItemRepository $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    public function notificationsCount(int $userId): int
    {
        return sizeof($this->itemRepository->findToBeNotifiedInAppOneUserByCleared($userId, false));
    }
}
