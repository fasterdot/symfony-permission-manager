<?php

/*
 * This file is part of the SymfonyPermissionManager project.
 *
 * (c) David Tondwa <david.tondwa@hotmail.com>
 *
 */
declare(strict_types=1);

namespace Dark\SymfonyPermissionManager\Attribute;

use Attribute;

/**
 * Attribut pour marquer les méthodes ou classes nécessitant une ou plusieurs permissions.
 *
 * Cet attribut peut être utilisé sur des méthodes de contrôleur ou des classes entières
 * pour indiquer les permissions requises pour y accéder.
 *
 * Exemple d'utilisation:
 * #[HasPermission('ROLE_ADMIN')]
 * #[HasPermission(['user_view', 'user_edit'])]
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class HasPermission
{
    /**
     * @param string|array $permission Le nom de la permission (string) ou un tableau de noms de permissions (array).
     * Si un tableau est fourni, l'utilisateur doit posséder au moins une des permissions listées.
     */
    public function __construct(
        public string|array $permission
    ) {}
}
