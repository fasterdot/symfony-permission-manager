# Guide de migration

## Migration vers la nouvelle architecture DDD

### 1. Implémenter les interfaces du domaine

Vos entités doivent maintenant implémenter les interfaces du domaine :

#### Entité User

```php
<?php

use Fasterdot\SymfonyPermissionManager\Domain\Interface\UserWithPermissionsInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, UserWithPermissionsInterface
{
    private ?Role $role = null;

    public function getRole(): ?RoleWithPermissionsInterface
    {
        return $this->role;
    }
    
    // ... autres méthodes UserInterface
}
```

#### Entité Role

```php
<?php

use Fasterdot\SymfonyPermissionManager\Domain\Interface\RoleWithPermissionsInterface;
use Doctrine\Common\Collections\Collection;

class Role implements RoleWithPermissionsInterface
{
    private Collection $permissions;

    public function getPermissions(): iterable
    {
        return $this->permissions;
    }
}
```

#### Entité Permission

```php
<?php

use Fasterdot\SymfonyPermissionManager\Domain\Interface\PermissionInterface;

class Permission implements PermissionInterface
{
    private string $code;

    public function getCode(): string
    {
        return $this->code;
    }
}
```

### 2. Configuration du bundle

Ajoutez la configuration dans `config/packages/fasterdot_symfony_permission_manager.yaml` :

```yaml
fasterdot_symfony_permission_manager:
    error_message: 'Accès refusé : permissions insuffisantes.'
    enable_cache: false
```

### 3. Utilisation

L'utilisation reste identique :

```php
use Fasterdot\SymfonyPermissionManager\Attribute\HasPermission;

#[HasPermission('user_view')]
class UserController
{
    #[HasPermission(['user_edit', 'user_delete'])]
    public function edit(): Response
    {
        // ...
    }
}
```

Dans Twig :

```twig
{% if has_permission('user_view') %}
    {# Contenu visible uniquement avec la permission #}
{% endif %}
```

### 4. Injection de dépendance

Si vous avez besoin d'injecter le service de vérification des permissions :

```php
use Fasterdot\SymfonyPermissionManager\Domain\Interface\PermissionCheckerInterface;

class MyService
{
    public function __construct(
        private PermissionCheckerInterface $permissionChecker
    ) {}
    
    public function someMethod(): void
    {
        if ($this->permissionChecker->can('some_permission')) {
            // ...
        }
    }
}
```

