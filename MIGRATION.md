# Guide de migration

Ce guide vous aide à migrer vers la nouvelle architecture DDD du package Symfony Permission Manager.

## Table des matières

1. [Migration depuis v0.2.x vers v0.3.0+](#migration-depuis-v02x-vers-v030)
2. [Implémentation des interfaces](#implémentation-des-interfaces)
3. [Utilisation des nouvelles fonctionnalités](#utilisation-des-nouvelles-fonctionnalités)
4. [Dépannage](#dépannage)

## Migration depuis v0.2.x vers v0.3.0+

### Changements principaux

- **Architecture DDD** : Refactorisation complète avec séparation Domain/Application/Infrastructure
- **Interfaces du domaine** : Vos entités doivent implémenter les nouvelles interfaces
- **Service renommé** : `PermissionHelper` → `PermissionCheckerService` (via interface)
- **Logique AND/OR** : Support des deux modes de vérification
- **Permissions contextuelles** : Support des permissions basées sur un contexte

### Étape 1 : Mettre à jour les dépendances

```bash
composer require fasterdot/symfony-permission-manager:^0.4.0
```

### Étape 2 : Implémenter les interfaces du domaine

Vos entités doivent maintenant implémenter les interfaces du domaine.

## Implémentation des interfaces

### Entité User

**Avant (v0.2.x) :**
```php
class User implements UserInterface
{
    private ?Role $role = null;

    public function getRole(): ?Role
    {
        return $this->role;
    }
}
```

**Après (v0.3.0+) :**
```php
<?php

namespace App\Entity;

use Fasterdot\SymfonyPermissionManager\Domain\Interface\UserWithPermissionsInterface;
use Fasterdot\SymfonyPermissionManager\Domain\Interface\RoleWithPermissionsInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, UserWithPermissionsInterface
{
    private ?Role $role = null;

    /**
     * Le type de retour doit être RoleWithPermissionsInterface
     */
    public function getRole(): ?RoleWithPermissionsInterface
    {
        return $this->role;
    }
    
    // ... autres méthodes UserInterface (getRoles(), getUserIdentifier(), etc.)
}
```

**Points importants :**
- Ajoutez `UserWithPermissionsInterface` à vos `implements`
- Le type de retour de `getRole()` doit être `?RoleWithPermissionsInterface` (pas `?Role`)

### Entité Role

**Avant (v0.2.x) :**
```php
class Role
{
    private Collection $permissions;

    public function getPermissions(): Collection
    {
        return $this->permissions;
    }
}
```

**Après (v0.3.0+) :**
```php
<?php

namespace App\Entity;

use Fasterdot\SymfonyPermissionManager\Domain\Interface\RoleWithPermissionsInterface;
use Fasterdot\SymfonyPermissionManager\Domain\Interface\PermissionInterface;
use Doctrine\Common\Collections\Collection;

class Role implements RoleWithPermissionsInterface
{
    /** @var Collection<int, Permission> */
    private Collection $permissions;

    /**
     * Le type de retour doit être iterable<PermissionInterface>
     */
    public function getPermissions(): iterable
    {
        return $this->permissions;
    }
}
```

**Points importants :**
- Ajoutez `RoleWithPermissionsInterface` à vos `implements`
- Le type de retour de `getPermissions()` doit être `iterable` (pas `Collection`)
- Les éléments retournés doivent implémenter `PermissionInterface`

### Entité Permission

**Avant (v0.2.x) :**
```php
class Permission
{
    private ?string $code = null;

    public function getCode(): ?string
    {
        return $this->code;
    }
}
```

**Après (v0.3.0+) :**
```php
<?php

namespace App\Entity;

use Fasterdot\SymfonyPermissionManager\Domain\Interface\PermissionInterface;

class Permission implements PermissionInterface
{
    private string $code;

    /**
     * Le type de retour doit être string (pas ?string)
     */
    public function getCode(): string
    {
        return $this->code;
    }
}
```

**Points importants :**
- Ajoutez `PermissionInterface` à vos `implements`
- Le type de retour de `getCode()` doit être `string` (pas `?string`)
- Assurez-vous que `$code` ne peut pas être `null` (ajoutez une contrainte NOT NULL en BDD si nécessaire)

### Exemple complet avec Doctrine

```php
<?php

namespace App\Entity;

use App\Repository\PermissionRepository;
use Doctrine\ORM\Mapping as ORM;
use Fasterdot\SymfonyPermissionManager\Domain\Interface\PermissionInterface;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
#[ORM\Table(name: 'permissions')]
class Permission implements PermissionInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true, nullable: false)]
    private string $code;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }
}
```

## Utilisation des nouvelles fonctionnalités

### Logique AND/OR

**Avant (v0.2.x) :**
```php
// Seulement OR disponible
#[HasPermission(['user_edit', 'user_delete'])]
```

**Après (v0.3.0+) :**
```php
// Mode OR (par défaut)
#[HasPermission(['user_edit', 'user_delete'])]

// Mode AND
#[HasPermission(['user_edit', 'user_delete'], mode: 'AND')]
```

### Permissions contextuelles (nouveau)

```php
#[HasPermission('article_edit', subject: 'article')]
public function edit(Article $article): Response
{
    // Le système vérifie automatiquement si l'utilisateur peut éditer cet article
}
```

Voir la section [Permissions contextuelles](#permissions-contextuelles) pour plus de détails.

### Injection de dépendance

**Avant (v0.2.x) :**
```php
use Fasterdot\SymfonyPermissionManager\Helper\PermissionHelper;

class MyService
{
    public function __construct(
        private PermissionHelper $permissionHelper
    ) {}
}
```

**Après (v0.3.0+) :**
```php
use Fasterdot\SymfonyPermissionManager\Domain\Interface\PermissionCheckerInterface;

class MyService
{
    public function __construct(
        private PermissionCheckerInterface $permissionChecker
    ) {}
    
    public function someMethod(): void
    {
        // Utilisation identique
        if ($this->permissionChecker->can('some_permission')) {
            // ...
        }
    }
}
```

**Points importants :**
- Utilisez `PermissionCheckerInterface` au lieu de `PermissionHelper`
- L'interface est automatiquement aliasée vers `PermissionCheckerService`
- Aucun changement dans la configuration des services nécessaire

## Permissions contextuelles

### Créer un vérificateur contextuel

1. Créez une classe qui implémente `ContextualPermissionCheckerInterface` :

```php
<?php

namespace App\Security;

use App\Entity\Article;
use Fasterdot\SymfonyPermissionManager\Domain\Interface\ContextualPermissionCheckerInterface;
use Fasterdot\SymfonyPermissionManager\Domain\Interface\UserWithPermissionsInterface;

class ArticlePermissionChecker implements ContextualPermissionCheckerInterface
{
    public function supports(string $permission, mixed $subject): bool
    {
        return $subject instanceof Article 
            && in_array($permission, ['article_edit', 'article_delete'], true);
    }

    public function can(UserWithPermissionsInterface $user, string $permission, mixed $subject): bool
    {
        if (!$subject instanceof Article) {
            return false;
        }

        // Votre logique métier ici
        return $subject->getAuthor()->getId() === $user->getId();
    }
}
```

2. Enregistrez le service dans `config/services.yaml` :

```yaml
services:
    App\Security\ArticlePermissionChecker:
        tags: ['fasterdot.permission.contextual_checker']
```

3. Utilisez dans vos contrôleurs :

```php
#[HasPermission('article_edit', subject: 'article')]
public function edit(Article $article): Response
{
    // ...
}
```

## Dépannage

### Erreur : "Cannot autowire service ... needs an instance of PermissionHelper"

**Cause :** Vous utilisez encore l'ancienne classe `PermissionHelper`.

**Solution :** Remplacez par `PermissionCheckerInterface` :

```php
// ❌ Ancien
use Fasterdot\SymfonyPermissionManager\Helper\PermissionHelper;
private PermissionHelper $permissionHelper

// ✅ Nouveau
use Fasterdot\SymfonyPermissionManager\Domain\Interface\PermissionCheckerInterface;
private PermissionCheckerInterface $permissionChecker
```

### Erreur : "Declaration of ... getCode(): ?string must be compatible with ... getCode(): string"

**Cause :** La méthode `getCode()` de votre entité `Permission` retourne `?string` au lieu de `string`.

**Solution :** Modifiez le type de retour et assurez-vous que `$code` n'est jamais `null` :

```php
// ❌ Ancien
public function getCode(): ?string
{
    return $this->code;
}

// ✅ Nouveau
public function getCode(): string
{
    return $this->code;
}
```

### Erreur : "Declaration of ... getRole(): ?Role must be compatible with ... getRole(): ?RoleWithPermissionsInterface"

**Cause :** Le type de retour de `getRole()` n'est pas compatible avec l'interface.

**Solution :** Changez le type de retour :

```php
// ❌ Ancien
public function getRole(): ?Role
{
    return $this->role;
}

// ✅ Nouveau
public function getRole(): ?RoleWithPermissionsInterface
{
    return $this->role;
}
```

### Les permissions ne fonctionnent pas après la migration

**Vérifications :**
1. Vos entités implémentent bien toutes les interfaces requises
2. Les types de retour sont corrects (`string` pour `getCode()`, `iterable` pour `getPermissions()`, etc.)
3. Le bundle est bien enregistré dans `config/bundles.php`
4. La configuration est présente dans `config/packages/fasterdot_symfony_permission_manager.yaml`

### Le subject n'est pas résolu dans les permissions contextuelles

**Cause :** Le nom du paramètre dans l'attribut ne correspond pas au nom du paramètre de la méthode.

**Solution :** Assurez-vous que les noms correspondent :

```php
// ✅ Correct
#[HasPermission('article_edit', subject: 'article')]
public function edit(Article $article): Response // Le paramètre s'appelle 'article'
{
    // ...
}

// ❌ Incorrect
#[HasPermission('article_edit', subject: 'article')]
public function edit(Article $art): Response // Le paramètre s'appelle 'art', pas 'article'
{
    // ...
}
```

## Checklist de migration

- [ ] Mise à jour du package vers v0.4.0+
- [ ] Entité `User` implémente `UserWithPermissionsInterface`
- [ ] Méthode `User::getRole()` retourne `?RoleWithPermissionsInterface`
- [ ] Entité `Role` implémente `RoleWithPermissionsInterface`
- [ ] Méthode `Role::getPermissions()` retourne `iterable`
- [ ] Entité `Permission` implémente `PermissionInterface`
- [ ] Méthode `Permission::getCode()` retourne `string` (pas `?string`)
- [ ] Remplacement de `PermissionHelper` par `PermissionCheckerInterface` dans les services
- [ ] Configuration du bundle ajoutée
- [ ] Tests effectués

## Support

Si vous rencontrez des problèmes lors de la migration, ouvrez une issue sur [GitHub](https://github.com/fasterdot/symfony-permission-manager) avec :
- La version du package utilisée
- Le message d'erreur complet
- Un exemple de code minimal reproduisant le problème
