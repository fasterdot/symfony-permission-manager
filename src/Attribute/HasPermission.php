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
 * #[HasPermission(['user_view', 'user_edit'])] // OR par défaut
 * #[HasPermission(['user_edit', 'user_delete'], mode: 'AND')] // Toutes les permissions requises
 * #[HasPermission('article_edit', subject: 'article')] // Permission contextuelle
 * 
 * Note: 
 * - Si un tableau est fourni avec mode='OR' (défaut), l'utilisateur doit posséder au moins une des permissions.
 * - Si mode='AND', l'utilisateur doit posséder toutes les permissions listées.
 * - Le paramètre 'subject' permet de vérifier des permissions contextuelles (ex: "peut éditer seulement ses propres articles").
 *   Le subject doit correspondre au nom d'un paramètre de la méthode du contrôleur.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class HasPermission
{
    public const MODE_OR = 'OR';
    public const MODE_AND = 'AND';

    /**
     * @param string|array<string> $permission Le nom de la permission (string) ou un tableau de noms de permissions (array).
     * @param string $mode Le mode de vérification : 'OR' (défaut) pour au moins une permission, 'AND' pour toutes les permissions.
     * @param string|null $subject Le nom du paramètre de la méthode qui contient le sujet du contexte (ex: 'article', 'user').
     *                             Si fourni, la vérification de permission sera contextuelle.
     * 
     * @throws \InvalidArgumentException Si la permission est vide ou invalide, ou si le mode est invalide.
     */
    public function __construct(
        public string|array $permission,
        public string $mode = self::MODE_OR,
        public ?string $subject = null
    ) {
        $this->validate();
    }

    /**
     * Valide que la permission n'est pas vide et que le mode est valide.
     *
     * @throws \InvalidArgumentException Si la permission est vide ou invalide, ou si le mode est invalide.
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

        // Valide le mode
        $validModes = [self::MODE_OR, self::MODE_AND];
        if (!in_array(strtoupper($this->mode), $validModes, true)) {
            throw new \InvalidArgumentException(
                sprintf('Le mode doit être "%s" ou "%s", "%s" fourni.', self::MODE_OR, self::MODE_AND, $this->mode)
            );
        }

        // Normalise le mode en majuscules
        $this->mode = strtoupper($this->mode);

        // Valide le subject si fourni
        if ($this->subject !== null && trim($this->subject) === '') {
            throw new \InvalidArgumentException('Le nom du subject ne peut pas être vide.');
        }
    }
}
