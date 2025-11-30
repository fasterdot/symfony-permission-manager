<?php

/*
 * This file is part of the SymfonyPermissionManager project.
 *
 * (c) David Tondwa <david.tondwa@hotmail.com>
 *
 */
declare(strict_types=1);

namespace Fasterdot\SymfonyPermissionManager\Application\Service;

use Fasterdot\SymfonyPermissionManager\Domain\Interface\PermissionCheckerInterface;
use Fasterdot\SymfonyPermissionManager\Domain\Interface\PermissionInterface;
use Fasterdot\SymfonyPermissionManager\Domain\Interface\RoleWithPermissionsInterface;
use Fasterdot\SymfonyPermissionManager\Domain\Interface\UserWithPermissionsInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Service d'application pour la vérification des permissions.
 * 
 * Ce service implémente la logique métier de vérification des permissions
 * en utilisant les interfaces du domaine pour découpler les dépendances.
 */
final readonly class PermissionCheckerService implements PermissionCheckerInterface
{
    public function __construct(
        private Security $security
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function can(string|array $permissionNames): bool
    {
        $user = $this->security->getUser();

        if (!$user instanceof UserWithPermissionsInterface) {
            return false;
        }

        $role = $user->getRole();

        if (!$role instanceof RoleWithPermissionsInterface) {
            return false;
        }

        $userPermissions = $this->extractPermissionCodes($role->getPermissions());
        $requiredPermissions = (array) $permissionNames;

        foreach ($requiredPermissions as $requiredPermission) {
            if (in_array($requiredPermission, $userPermissions, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extrait les codes des permissions d'une collection.
     *
     * @param iterable<PermissionInterface> $permissions La collection de permissions.
     * @return string[] Un tableau des codes de permissions.
     */
    private function extractPermissionCodes(iterable $permissions): array
    {
        $codes = [];
        
        foreach ($permissions as $permission) {
            if ($permission instanceof PermissionInterface) {
                $codes[] = $permission->getCode();
            }
        }

        return $codes;
    }
}

