<?php

/*
 * This file is part of the SymfonyPermissionManager project.
 *
 * (c) David Tondwa <david.tondwa@hotmail.com>
 *
 */
declare(strict_types=1);

namespace Fasterdot\SymfonyPermissionManager\Domain\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Exception levée lorsqu'un utilisateur n'a pas les permissions requises.
 * 
 * Cette exception étend AccessDeniedHttpException pour maintenir la compatibilité
 * avec Symfony tout en fournissant un message d'erreur sécurisé.
 * 
 * Le message par défaut peut être personnalisé via la configuration du bundle.
 */
class PermissionDeniedException extends AccessDeniedHttpException
{
    public function __construct(
        string $message = 'Accès refusé : permissions insuffisantes.',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $previous);
    }
}

