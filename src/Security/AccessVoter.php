<?php

namespace App\Security;

use App\Entity\Category;
use App\Entity\Item;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AccessVoter extends Voter
{
    const ACCESS = 'access';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if ($attribute != self::ACCESS)
            return false;

        if(!$subject instanceof Category
            && !$subject instanceof Item
            && !$subject instanceof User)
            return false;

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        if($subject instanceof Category)
            return $subject->getOwner()->getUserIdentifier() === $user->getUserIdentifier();

        if($subject instanceof Item)
            return $subject->getCategory()->getOwner()->getUserIdentifier() === $user->getUserIdentifier();

        if($subject instanceof User)
            return $subject->getUserIdentifier() === $user->getUserIdentifier();

        // This should never be reached
        return false;
    }
}