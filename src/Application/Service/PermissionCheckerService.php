<?php

/*
 * This file is part of the SymfonyPermissionManager project.
 *
 * (c) David Tondwa <david.tondwa@hotmail.com>
 *
 */
declare(strict_types=1);

namespace Fasterdot\SymfonyPermissionManager\Application\Service;

use Fasterdot\SymfonyPermissionManager\Domain\Interface\ContextualPermissionCheckerInterface;
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
    /**
     * @param Security $security Le service de sécurité Symfony.
     * @param iterable<ContextualPermissionCheckerInterface> $contextualCheckers Les vérificateurs de permissions contextuelles.
     */
    public function __construct(
        private Security $security,
        private iterable $contextualCheckers = []
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function can(string|array $permissionNames, string $mode = self::MODE_OR, mixed $subject = null): bool
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

        // Normalise le mode
        $mode = strtoupper($mode);

        // Si un subject est fourni, utilise les vérificateurs contextuels
        if ($subject !== null) {
            return $this->checkContextualPermissions($user, $requiredPermissions, $mode, $subject, $userPermissions);
        }

        // Vérification non-contextuelle (basée uniquement sur les permissions du rôle)
        return $this->checkNonContextualPermissions($requiredPermissions, $mode, $userPermissions);
    }

    /**
     * Vérifie les permissions contextuelles.
     *
     * @param UserWithPermissionsInterface $user L'utilisateur.
     * @param string[] $requiredPermissions Les permissions requises.
     * @param string $mode Le mode de vérification (OR ou AND).
     * @param mixed $subject Le sujet du contexte.
     * @param string[] $userPermissions Les permissions de l'utilisateur.
     * @return bool Vrai si l'utilisateur a les permissions requises selon le contexte.
     */
    private function checkContextualPermissions(
        UserWithPermissionsInterface $user,
        array $requiredPermissions,
        string $mode,
        mixed $subject,
        array $userPermissions
    ): bool {
        // Si une seule permission est requise
        if (count($requiredPermissions) === 1) {
            $permission = $requiredPermissions[0];
            
            // Vérifie d'abord si l'utilisateur a la permission de base
            if (!in_array($permission, $userPermissions, true)) {
                return false;
            }

            // Cherche un vérificateur contextuel qui supporte cette permission et ce subject
            foreach ($this->contextualCheckers as $checker) {
                if ($checker->supports($permission, $subject)) {
                    return $checker->can($user, $permission, $subject);
                }
            }

            // Si aucun vérificateur contextuel ne supporte, on accepte (l'utilisateur a la permission de base)
            return true;
        }

        // Plusieurs permissions requises
        if ($mode === self::MODE_AND) {
            // Toutes les permissions doivent être satisfaites
            foreach ($requiredPermissions as $permission) {
                if (!in_array($permission, $userPermissions, true)) {
                    return false;
                }

                // Vérifie le contexte si un vérificateur le supporte
                foreach ($this->contextualCheckers as $checker) {
                    if ($checker->supports($permission, $subject)) {
                        if (!$checker->can($user, $permission, $subject)) {
                            return false;
                        }
                        continue 2; // Permission vérifiée, passe à la suivante
                    }
                }
            }
            return true;
        }

        // Mode OR : au moins une permission doit être satisfaite
        foreach ($requiredPermissions as $permission) {
            if (!in_array($permission, $userPermissions, true)) {
                continue;
            }

            // Vérifie le contexte si un vérificateur le supporte
            $contextualCheck = true;
            foreach ($this->contextualCheckers as $checker) {
                if ($checker->supports($permission, $subject)) {
                    $contextualCheck = $checker->can($user, $permission, $subject);
                    break;
                }
            }

            if ($contextualCheck) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie les permissions non-contextuelles (basées uniquement sur les permissions du rôle).
     *
     * @param string[] $requiredPermissions Les permissions requises.
     * @param string $mode Le mode de vérification (OR ou AND).
     * @param string[] $userPermissions Les permissions de l'utilisateur.
     * @return bool Vrai si l'utilisateur a les permissions requises.
     */
    private function checkNonContextualPermissions(array $requiredPermissions, string $mode, array $userPermissions): bool
    {
        // Si une seule permission est requise, le mode n'a pas d'importance
        if (count($requiredPermissions) === 1) {
            return in_array($requiredPermissions[0], $userPermissions, true);
        }

        // Logique AND : l'utilisateur doit avoir toutes les permissions
        if ($mode === self::MODE_AND) {
            foreach ($requiredPermissions as $requiredPermission) {
                if (!in_array($requiredPermission, $userPermissions, true)) {
                    return false;
                }
            }
            return true;
        }

        // Logique OR (par défaut) : l'utilisateur doit avoir au moins une permission
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

