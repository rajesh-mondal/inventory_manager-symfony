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
    public const VIEW = 'INVENTORY_VIEW';
    public const DELETE = 'INVENTORY_DELETE';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Inventory;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        $inventory = $subject;

        // ADMINS: Full access
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // GUESTS: Can only VIEW if public. Cannot Edit or Delete anything.
        if (!$user instanceof User) {
            return $attribute === self::VIEW && $inventory->isPublic();
        }

        // AUTHENTICATED USERS (Owners and Standard Users)
        switch ($attribute) {
            case self::VIEW:
                // return $inventory->isPublic() || ($inventory->getCreator() === $user);
                return $inventory->isPublic() || ($user === $inventory->getCreator());

            case self::EDIT:
                // Owner can edit OR any logged-in user can edit IF it is public
                return ($inventory->getCreator() === $user) || $inventory->isPublic();

            case self::DELETE:
                // Strictly the owner only
                return $inventory->getCreator() === $user;
        }

        return false;
    }
}
