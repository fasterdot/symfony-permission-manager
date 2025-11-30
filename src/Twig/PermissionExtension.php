<?php

/*
 * This file is part of the SymfonyPermissionManager project.
 *
 * (c) David Tondwa <david.tondwa@hotmail.com>
 *
 */
declare(strict_types=1);

namespace Fasterdot\SymfonyPermissionManager\Twig;

use Fasterdot\SymfonyPermissionManager\Domain\Interface\PermissionCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Extension Twig pour vérifier les permissions dans les templates.
 *
 * Cette extension expose la méthode `can` du `PermissionCheckerInterface`
 * sous forme de fonction Twig `has_permission`.
 */
class PermissionExtension extends AbstractExtension
{
    /**
     * @param PermissionCheckerInterface $permissionChecker Le service de vérification des permissions.
     */
    public function __construct(private readonly PermissionCheckerInterface $permissionChecker) {}

    /**
     * Retourne les fonctions Twig fournies par cette extension.
     *
     * @return TwigFunction[] Un tableau d'objets TwigFunction.
     */
    public function getFunctions(): array
    {
        return [
            // Enregistre la fonction Twig 'has_permission' qui appelle la méthode 'can' du PermissionChecker.
            new TwigFunction('has_permission', [$this->permissionChecker, 'can']),
        ];
    }
}
