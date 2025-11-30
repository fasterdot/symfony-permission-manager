<?php

/*
 * This file is part of the SymfonyPermissionManager project.
 *
 * (c) David Tondwa <david.tondwa@hotmail.com>
 *
 */
declare(strict_types=1);

namespace Fasterdot\SymfonyPermissionManager\Domain\Interface;

/**
 * Interface pour les rôles qui possèdent des permissions.
 * 
 * Les entités Role de l'application consommatrice doivent implémenter cette interface
 * pour être compatibles avec le système de permissions.
 */
interface RoleWithPermissionsInterface
{
    /**
     * Retourne la collection des permissions du rôle.
     *
     * @return PermissionInterface[]|iterable La collection des permissions.
     */
    public function getPermissions(): iterable;
}

