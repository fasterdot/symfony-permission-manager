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
    public const MODE_OR = 'OR';
    public const MODE_AND = 'AND';

    /**
     * Vérifie si l'utilisateur actuellement connecté possède les permissions spécifiées.
     *
     * @param string|string[] $permissionNames Un nom de permission unique (string) ou un tableau de noms de permissions (string[]).
     * @param string $mode Le mode de vérification : 'OR' (défaut) pour au moins une permission, 'AND' pour toutes les permissions.
     * @param mixed $subject Le sujet du contexte pour les permissions contextuelles (ex: une entité Article, User, etc.).
     *                       Si null, la vérification est non-contextuelle (basée uniquement sur les permissions du rôle).
     * @return bool 
     *   - En mode OR : Vrai si l'utilisateur a au moins une des permissions, faux sinon.
     *   - En mode AND : Vrai si l'utilisateur a toutes les permissions, faux sinon.
     *   - Si un subject est fourni, les vérificateurs contextuels sont également consultés.
     */
    public function can(string|array $permissionNames, string $mode = self::MODE_OR, mixed $subject = null): bool;
}

