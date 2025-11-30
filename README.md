# Symfony Permission Manager

Le package **Symfony Permission Manager** est une solution Composer conçue pour offrir une gestion **flexible et robuste des permissions et des accès** au sein de vos applications Symfony. Il centralise la **définition, l'attribution et la vérification des permissions**, permettant un **contrôle d'accès granulaire** basé sur les rôles ou des critères plus complexes.

## Caractéristiques principales

* **Architecture DDD** : Conçu selon les principes du Domain-Driven Design pour une meilleure maintenabilité
* **Interfaces du domaine** : Découplage complet via des interfaces pour une intégration flexible
* **Définition de Permissions** : Créez et structurez des permissions personnalisées pour répondre aux besoins spécifiques de votre application
* **Attribution Flexible** : Attribuez facilement des permissions aux utilisateurs ou aux rôles, avec des options pour des permissions directes ou héritées
* **Vérification Simplifiée** : Vérifiez aisément si un utilisateur possède une permission donnée, que ce soit dans vos contrôleurs, services ou templates Twig
* **Intégration Symfony** : S'intègre naturellement avec le système de sécurité de Symfony, exploitant ses mécanismes existants pour une compatibilité optimale
* **Configuration personnalisable** : Messages d'erreur et options de cache configurables
* **Extensible** : Conçu pour être facilement extensible, il permet aux développeurs d'ajouter des logiques de permission personnalisées ou de s'intégrer à d'autres systèmes

## Installation

```bash
composer require fasterdot/symfony-permission-manager
```

## Configuration

Créez le fichier `config/packages/fasterdot_symfony_permission_manager.yaml` :

```yaml
fasterdot_symfony_permission_manager:
    error_message: 'Accès refusé : permissions insuffisantes.'
    enable_cache: false
```

## Utilisation

### 1. Implémenter les interfaces du domaine

Vos entités doivent implémenter les interfaces du domaine. Voir le fichier [MIGRATION.md](MIGRATION.md) pour plus de détails.

### 2. Utiliser l'attribut HasPermission

```php
use Fasterdot\SymfonyPermissionManager\Attribute\HasPermission;

#[HasPermission('user_view')]
class UserController
{
    #[HasPermission(['user_edit', 'user_delete'])]
    public function edit(): Response
    {
        // L'utilisateur doit avoir 'user_edit' OU 'user_delete'
    }
}
```

### 3. Utiliser dans Twig

```twig
{% if has_permission('user_view') %}
    <p>Contenu visible uniquement avec la permission user_view</p>
{% endif %}

{% if has_permission(['user_edit', 'user_delete']) %}
    <p>Contenu visible avec user_edit OU user_delete</p>
{% endif %}
```

### 4. Injection de dépendance

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

## Architecture

Le package suit une architecture Domain-Driven Design (DDD) :

- **Domain** : Interfaces et exceptions du domaine
- **Application** : Services d'application (logique métier)
- **Infrastructure** : Intégrations Symfony (EventListeners, Twig, etc.)

## Migration

Si vous migrez depuis une version antérieure, consultez le fichier [MIGRATION.md](MIGRATION.md).

## Changelog

Consultez le fichier [CHANGELOG.md](CHANGELOG.md) pour la liste des améliorations.

## Licence

MIT
