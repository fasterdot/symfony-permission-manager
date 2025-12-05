<?php

/*
 * This file is part of the SymfonyPermissionManager project.
 *
 * (c) David Tondwa <david.tondwa@hotmail.com>
 *
 */
declare(strict_types=1);

namespace Fasterdot\SymfonyPermissionManager\Domain\Interface;

use Fasterdot\SymfonyPermissionManager\Domain\Interface\UserWithPermissionsInterface;

/**
 * Interface pour les vérificateurs de permissions contextuelles.
 * 
 * Cette interface permet de créer des vérificateurs personnalisés pour des permissions
 * basées sur un contexte (subject). Par exemple, vérifier si un utilisateur peut éditer
 * seulement ses propres articles.
 * 
 * Les classes implémentant cette interface doivent être enregistrées comme services Symfony
 * et taguées avec 'fasterdot.permission.contextual_checker'.
 */
interface ContextualPermissionCheckerInterface
{
    /**
     * Vérifie si l'utilisateur a la permission pour le subject donné.
     *
     * @param UserWithPermissionsInterface $user L'utilisateur à vérifier.
     * @param string $permission Le nom de la permission à vérifier.
     * @param mixed $subject Le sujet du contexte (objet, entité, etc.).
     * @return bool Vrai si l'utilisateur a la permission pour ce subject, faux sinon.
     */
    public function can(UserWithPermissionsInterface $user, string $permission, mixed $subject): bool;

    /**
     * Indique si ce vérificateur supporte la permission et le subject donnés.
     *
     * @param string $permission Le nom de la permission.
     * @param mixed $subject Le sujet du contexte.
     * @return bool Vrai si ce vérificateur peut gérer cette combinaison permission/subject.
     */
    public function supports(string $permission, mixed $subject): bool;
}

