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
 * Interface pour la vérification des permissions.
 * 
 * Cette interface définit le contrat pour vérifier si un utilisateur
 * possède les permissions requises.
 */
interface PermissionCheckerInterface
{
    /**
     * Vérifie si l'utilisateur actuellement connecté possède au moins une des permissions spécifiées.
     *
     * @param string|string[] $permissionNames Un nom de permission unique (string) ou un tableau de noms de permissions (string[]).
     * @return bool Vrai si l'utilisateur a au moins une des permissions, faux sinon.
     */
    public function can(string|array $permissionNames): bool;
}

