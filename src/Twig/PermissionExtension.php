<?php

/*
 * This file is part of the SymfonyPermissionManager project.
 *
 * (c) David Tondwa <david.tondwa@hotmail.com>
 *
 */
declare(strict_types=1);

namespace Dark\SymfonyPermissionManager\Twig;

use Dark\SymfonyPermissionManager\Helper\PermissionHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Extension Twig pour vérifier les permissions dans les templates.
 *
 * Cette extension expose la méthode `can` du `PermissionHelper`
 * sous forme de fonction Twig `has_permission`.
 */
class PermissionExtension extends AbstractExtension
{
    /**
     * @param PermissionHelper $permissionHelper Le service d'aide aux permissions.
     */
    public function __construct(private readonly PermissionHelper $permissionHelper) {}

    /**
     * Retourne les fonctions Twig fournies par cette extension.
     *
     * @return TwigFunction[] Un tableau d'objets TwigFunction.
     */
    public function getFunctions(): array
    {
        return [
            // Enregistre la fonction Twig 'has_permission' qui appelle la méthode 'can' du PermissionHelper.
            new TwigFunction('has_permission', [$this->permissionHelper, 'can']),
        ];
    }
}
