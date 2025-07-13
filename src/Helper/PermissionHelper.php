<?php

/*
 * This file is part of the SymfonyPermissionManager project.
 *
 * (c) David Tondwa <david.tondwa@hotmail.com>
 *
 */
declare(strict_types=1);

namespace Fasterdot\SymfonyPermissionManager\Helper;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Aide à la vérification des permissions pour l'utilisateur actuellement connecté.
 *
 * Cette classe fournit une méthode simple pour vérifier si l'utilisateur
 * possède les permissions requises, en s'appuyant sur le système de sécurité
 * de Symfony (notamment les Voters).
 *
 * L'application consommatrice de ce package devra implémenter des Voters
 * Symfony pour définir la logique de vérification des permissions réelles
 * basées sur ses propres entités (ex: AfUser, Role, Permission).
 */
readonly class PermissionHelper
{
    /**
     * @param Security $security Le service de sécurité de Symfony pour accéder à l'utilisateur courant et vérifier les permissions.
     */
    public function __construct(private Security $security)
    {
    }

    /**
     * Vérifie si l'utilisateur actuellement connecté possède au moins une des permissions spécifiées.
     *
     * Cette méthode utilise `Security->isGranted()` pour déléguer la vérification
     * des permissions au système de sécurité de Symfony. Les "permissions" ici
     * sont des "attributs" au sens de Symfony Security (ex: 'manage_products', 'edit_post').
     *
     * @param string|string[] $permissionNames Un nom de permission unique (string) ou un tableau de noms de permissions (string[]).
     * Si un tableau est fourni, la méthode retourne true si l'utilisateur
     * possède au moins une des permissions listées.
     * @return bool Vrai si l'utilisateur a la permission, faux sinon.
     */
    public function can(string|array $permissionNames): bool
    {
        // Récupère l'utilisateur Symfony courant.
        $user = $this->security->getUser();

        // Si l'utilisateur n'est pas connecté, il n'a pas les permissions.
        if (!$user instanceof UserInterface) { // Vérifie contre l'interface générique UserInterface
            return false;
        }

        $role = $user->getRole();

        if (!$role) {
            return false;
        }

        $userPermissions = array_map(
            fn($perm) => $perm->getName(),
            $role->getPermissions()->toArray()
        );

        // Convertit les permissions requises en tableau pour une manipulation uniforme.
        $permissionNames = (array) $permissionNames;

        foreach ($permissionNames as $perm) {
            if (in_array($perm, $userPermissions, true)) {
                return true;
            }
        }

        return false; // L'utilisateur ne possède aucune des permissions requises.
    }
}
