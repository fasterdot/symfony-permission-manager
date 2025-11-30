<?php

/*
 * This file is part of the SymfonyPermissionManager project.
 *
 * (c) David Tondwa <david.tondwa@hotmail.com>
 *
 */
declare(strict_types=1);

namespace Fasterdot\SymfonyPermissionManager\Attribute;

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
 * 
 * Note: Si un tableau est fourni, l'utilisateur doit posséder au moins une des permissions listées (logique OR).
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class HasPermission
{
    /**
     * @param string|array<string> $permission Le nom de la permission (string) ou un tableau de noms de permissions (array).
     * Si un tableau est fourni, l'utilisateur doit posséder au moins une des permissions listées.
     * 
     * @throws \InvalidArgumentException Si la permission est vide ou invalide.
     */
    public function __construct(
        public string|array $permission
    ) {
        $this->validate();
    }

    /**
     * Valide que la permission n'est pas vide.
     *
     * @throws \InvalidArgumentException Si la permission est vide ou invalide.
     */
    private function validate(): void
    {
        if (is_string($this->permission)) {
            if (trim($this->permission) === '') {
                throw new \InvalidArgumentException('La permission ne peut pas être vide.');
            }
        } elseif (is_array($this->permission)) {
            if (empty($this->permission)) {
                throw new \InvalidArgumentException('Le tableau de permissions ne peut pas être vide.');
            }
            foreach ($this->permission as $perm) {
                if (!is_string($perm) || trim($perm) === '') {
                    throw new \InvalidArgumentException('Toutes les permissions doivent être des chaînes non vides.');
                }
            }
        }
    }
}
