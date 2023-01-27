<?php

namespace App\Factory;

use App\Entity\Item;
use App\Entity\User;

class ItemFactory
{
    public static function createItem(User $user): Item
    {
        $item = new Item();
        $item->setPurchase(new \DateTime());
        $item->setAmount(1);
        $item->setCurrency($user->getDefaultCurrency());
        return $item;
    }


}