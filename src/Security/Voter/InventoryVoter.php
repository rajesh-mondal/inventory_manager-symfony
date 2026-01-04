<?php

namespace App\Security\Voter;

use App\Entity\Inventory;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class InventoryVoter extends Voter
{
    public const EDIT = 'INVENTORY_EDIT';
    // public const VIEW = 'POST_VIEW';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        // This voter only cares about the INVENTORY_EDIT attribute and Inventory objects
        return $attribute === self::EDIT && $subject instanceof Inventory;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        $inventory = $subject;

        // ADMINS: Full access to everything
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // NON-AUTHENTICATED: Return false (they can't edit/modify)
        if (!$user instanceof User) {
            return false;
        }

        // CREATORS: Can edit their own inventories
        if ($inventory->getCreator() === $user) {
            return true;
        }

        // PUBLIC ACCESS: If the inventory is marked public, any auth user can edit
        if ($inventory->isPublic()) {
            return true;
        }

        return false;
    }
}
