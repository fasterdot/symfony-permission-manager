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
 * Interface pour les permissions.
 * 
 * Les entités Permission de l'application consommatrice doivent implémenter cette interface
 * pour être compatibles avec le système de permissions.
 */
interface PermissionInterface
{
    /**
     * Retourne le code unique de la permission.
     *
     * @return string Le code de la permission.
     */
    public function getCode(): string;
}

