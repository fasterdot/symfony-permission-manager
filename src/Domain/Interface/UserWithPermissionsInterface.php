<?php

/*
 * This file is part of the SymfonyPermissionManager project.
 *
 * (c) David Tondwa <david.tondwa@hotmail.com>
 *
 */
declare(strict_types=1);

namespace Fasterdot\SymfonyPermissionManager\Domain\Interface;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Interface pour les utilisateurs qui possèdent des permissions.
 * 
 * Les entités User de l'application consommatrice doivent implémenter cette interface
 * pour être compatibles avec le système de permissions.
 */
interface UserWithPermissionsInterface extends UserInterface
{
    /**
     * Retourne le rôle de l'utilisateur.
     *
     * @return RoleWithPermissionsInterface|null Le rôle de l'utilisateur ou null si aucun rôle n'est assigné.
     */
    public function getRole(): ?RoleWithPermissionsInterface;
}

