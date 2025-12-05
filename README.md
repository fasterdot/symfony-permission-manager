# Symfony Permission Manager

Le package **Symfony Permission Manager** est une solution Composer conçue pour offrir une gestion **flexible et robuste des permissions et des accès** au sein de vos applications Symfony. Il centralise la **définition, l'attribution et la vérification des permissions**, permettant un **contrôle d'accès granulaire** basé sur les rôles ou des critères plus complexes.

## Caractéristiques principales

* **Architecture DDD** : Conçu selon les principes du Domain-Driven Design pour une meilleure maintenabilité
* **Interfaces du domaine** : Découplage complet via des interfaces pour une intégration flexible
* **Logique AND/OR** : Support des deux modes de vérification - OR (au moins une permission) ou AND (toutes les permissions)
* **Permissions contextuelles** : Support des permissions basées sur un contexte (subject) pour des règles métier avancées
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

## Guide de démarrage rapide

### Étape 1 : Implémenter les interfaces du domaine

Vos entités doivent implémenter les interfaces du domaine pour être compatibles avec le package.

#### Entité User

```php
<?php

namespace App\Entity;

use Fasterdot\SymfonyPermissionManager\Domain\Interface\UserWithPermissionsInterface;
use Fasterdot\SymfonyPermissionManager\Domain\Interface\RoleWithPermissionsInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, UserWithPermissionsInterface
{
    private ?Role $role = null;

    public function getRole(): ?RoleWithPermissionsInterface
    {
        return $this->role;
    }

    // ... autres méthodes UserInterface (getRoles(), getUserIdentifier(), etc.)
}
```

#### Entité Role

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
     * @return iterable<PermissionInterface>
     */
    public function getPermissions(): iterable
    {
        return $this->permissions;
    }
}
```

#### Entité Permission

```php
<?php

namespace App\Entity;

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

### Étape 2 : Utiliser dans vos contrôleurs

```php
<?php

namespace App\Controller;

use Fasterdot\SymfonyPermissionManager\Attribute\HasPermission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/users', name: 'user_list')]
    #[HasPermission('view_users_list')]
    public function list(): Response
    {
        // Seuls les utilisateurs avec la permission 'view_users_list' peuvent accéder
        return $this->render('users/list.html.twig');
    }

    #[Route('/users/{id}/edit', name: 'user_edit')]
    #[HasPermission(['edit_user', 'manage_users'])] // OR : au moins une des deux
    public function edit(int $id): Response
    {
        // L'utilisateur doit avoir 'edit_user' OU 'manage_users'
        return $this->render('users/edit.html.twig');
    }

    #[Route('/users/{id}/delete', name: 'user_delete')]
    #[HasPermission(['edit_user', 'delete_user'], mode: 'AND')] // AND : les deux requises
    public function delete(int $id): Response
    {
        // L'utilisateur doit avoir 'edit_user' ET 'delete_user'
        return $this->render('users/delete.html.twig');
    }
}
```

### Étape 3 : Utiliser dans Twig

```twig
{# Vérification simple #}
{% if has_permission('view_users_list') %}
    <a href="{{ path('user_list') }}">Liste des utilisateurs</a>
{% endif %}

{# Vérification avec OR (par défaut) #}
{% if has_permission(['edit_user', 'manage_users']) %}
    <a href="{{ path('user_edit', {id: user.id}) }}">Modifier</a>
{% endif %}

{# Vérification avec AND #}
{% if has_permission(['edit_user', 'delete_user'], 'AND') %}
    <a href="{{ path('user_delete', {id: user.id}) }}">Supprimer</a>
{% endif %}
```

## Utilisation avancée

### Permissions contextuelles

Les permissions contextuelles permettent de vérifier des permissions basées sur un contexte (subject). Par exemple, vérifier si un utilisateur peut éditer seulement ses propres articles.

#### Exemple : Contrôleur avec permission contextuelle

```php
<?php

namespace App\Controller;

use App\Entity\Article;
use Fasterdot\SymfonyPermissionManager\Attribute\HasPermission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticleController extends AbstractController
{
    #[Route('/articles/{id}/edit', name: 'article_edit')]
    #[HasPermission('article_edit', subject: 'article')]
    public function edit(Article $article): Response
    {
        // Le système vérifie automatiquement si l'utilisateur peut éditer cet article spécifique
        // via un ContextualPermissionChecker (voir ci-dessous)
        return $this->render('articles/edit.html.twig', [
            'article' => $article
        ]);
    }
}
```

#### Créer un vérificateur contextuel personnalisé

```php
<?php

namespace App\Security;

use App\Entity\Article;
use App\Entity\User;
use Fasterdot\SymfonyPermissionManager\Domain\Interface\ContextualPermissionCheckerInterface;
use Fasterdot\SymfonyPermissionManager\Domain\Interface\UserWithPermissionsInterface;

class ArticlePermissionChecker implements ContextualPermissionCheckerInterface
{
    /**
     * Indique si ce vérificateur peut gérer cette combinaison permission/subject.
     */
    public function supports(string $permission, mixed $subject): bool
    {
        return $subject instanceof Article 
            && in_array($permission, ['article_edit', 'article_delete'], true);
    }

    /**
     * Vérifie si l'utilisateur a la permission pour ce subject.
     * 
     * ICI VOUS DÉFINISSEZ VOTRE LOGIQUE MÉTIER :
     * - L'article appartient à l'utilisateur ?
     * - L'utilisateur est admin ?
     * - L'utilisateur fait partie de l'équipe du projet ?
     * etc.
     */
    public function can(UserWithPermissionsInterface $user, string $permission, mixed $subject): bool
    {
        if (!$subject instanceof Article) {
            return false;
        }

        $article = $subject;

        // Règle 1 : L'auteur peut toujours éditer/supprimer ses articles
        if ($article->getAuthor() instanceof User && $article->getAuthor()->getId() === $user->getId()) {
            return true;
        }

        // Règle 2 : Les admins peuvent tout faire
        if ($this->userHasRole($user, 'ROLE_ADMIN')) {
            return true;
        }

        // Règle 3 : Les modérateurs peuvent éditer les articles publiés
        if ($permission === 'article_edit' && $article->isPublished()) {
            return $this->userHasRole($user, 'ROLE_MODERATOR');
        }

        // Règle 4 : Les membres de l'équipe peuvent éditer les articles de leur équipe
        if ($article->getTeam() && $article->getTeam()->hasMember($user)) {
            return true;
        }

        return false;
    }

    private function userHasRole(UserWithPermissionsInterface $user, string $role): bool
    {
        // Adaptez selon votre implémentation
        return in_array($role, $user->getRoles(), true);
    }
}
```

#### Enregistrer le vérificateur contextuel

Dans `config/services.yaml` :

```yaml
services:
    App\Security\ArticlePermissionChecker:
        tags: ['fasterdot.permission.contextual_checker']
```

Le tag `fasterdot.permission.contextual_checker` est automatiquement détecté par le package.

### Injection de dépendance dans les services

```php
<?php

namespace App\Service;

use Fasterdot\SymfonyPermissionManager\Domain\Interface\PermissionCheckerInterface;

class MyService
{
    public function __construct(
        private PermissionCheckerInterface $permissionChecker
    ) {}
    
    public function someMethod(): void
    {
        // Mode OR (par défaut) : au moins une permission
        if ($this->permissionChecker->can(['permission1', 'permission2'])) {
            // L'utilisateur a au moins une des permissions
        }
        
        // Mode AND : toutes les permissions requises
        if ($this->permissionChecker->can(
            ['permission1', 'permission2'], 
            PermissionCheckerInterface::MODE_AND
        )) {
            // L'utilisateur a toutes les permissions
        }
        
        // Permission contextuelle
        $article = $this->articleRepository->find($id);
        if ($this->permissionChecker->can(
            'article_edit', 
            PermissionCheckerInterface::MODE_OR, 
            $article
        )) {
            // Vérifie si l'utilisateur peut éditer cet article spécifique
        }
    }
}
```

## Cas d'usage courants

### Cas 1 : Protection d'une route simple

```php
#[Route('/admin/dashboard', name: 'admin_dashboard')]
#[HasPermission('view_admin_dashboard')]
public function dashboard(): Response
{
    // Seuls les utilisateurs avec 'view_admin_dashboard' peuvent accéder
}
```

### Cas 2 : Plusieurs permissions avec OR

```php
#[Route('/users', name: 'user_list')]
#[HasPermission(['view_users_list', 'manage_users'])] // OR par défaut
public function list(): Response
{
    // L'utilisateur doit avoir 'view_users_list' OU 'manage_users'
}
```

### Cas 3 : Plusieurs permissions avec AND

```php
#[Route('/users/{id}/delete', name: 'user_delete')]
#[HasPermission(['edit_user', 'delete_user'], mode: 'AND')]
public function delete(int $id): Response
{
    // L'utilisateur doit avoir 'edit_user' ET 'delete_user'
}
```

### Cas 4 : Permission contextuelle simple

```php
#[Route('/articles/{id}/edit', name: 'article_edit')]
#[HasPermission('article_edit', subject: 'article')]
public function edit(Article $article): Response
{
    // Vérifie si l'utilisateur peut éditer cet article spécifique
    // via un ContextualPermissionChecker
}
```

### Cas 5 : Permission contextuelle avec plusieurs permissions

```php
#[Route('/articles/{id}', name: 'article_show')]
#[HasPermission(['article_view', 'article_view_private'], subject: 'article')]
public function show(Article $article): Response
{
    // L'utilisateur doit avoir 'article_view' OU 'article_view_private'
    // ET le vérificateur contextuel doit autoriser l'accès à cet article
}
```

### Cas 6 : Utilisation dans Twig pour afficher conditionnellement

```twig
{# Afficher un bouton seulement si l'utilisateur a la permission #}
{% if has_permission('edit_user') %}
    <a href="{{ path('user_edit', {id: user.id}) }}" class="btn btn-primary">
        Modifier
    </a>
{% endif %}

{# Afficher un menu seulement si l'utilisateur a au moins une des permissions #}
{% if has_permission(['view_users', 'manage_users', 'view_reports']) %}
    <nav class="admin-menu">
        {# ... #}
    </nav>
{% endif %}
```

## Architecture

Le package suit une architecture Domain-Driven Design (DDD) :

- **Domain** : Interfaces et exceptions du domaine (`Domain/Interface/`, `Domain/Exception/`)
- **Application** : Services d'application (logique métier) (`Application/Service/`)
- **Infrastructure** : Intégrations Symfony (EventListeners, Twig, etc.) (`EventListener/`, `Twig/`)

Cette architecture permet :
- Un découplage total avec vos entités métier
- Une testabilité accrue
- Une maintenabilité améliorée
- Une extensibilité facilitée

## FAQ

### Comment migrer depuis une version antérieure ?

Consultez le fichier [MIGRATION.md](MIGRATION.md) pour un guide détaillé.

### Puis-je utiliser ce package avec les voters Symfony ?

Oui ! Les deux approches sont complémentaires :
- **Ce package** : Idéal pour les permissions simples basées sur des codes en BDD
- **Voters Symfony** : Meilleur pour des règles métier complexes et expressions avancées

Vous pouvez même créer un voter Symfony qui utilise ce package en interne.

### Comment fonctionnent les permissions contextuelles ?

1. L'utilisateur doit avoir la permission de base (ex: `article_edit`)
2. Si un `subject` est fourni, le système cherche un `ContextualPermissionChecker` qui supporte cette combinaison
3. Si trouvé, le vérificateur exécute votre logique métier personnalisée
4. Si non trouvé, l'accès est autorisé si la permission de base est présente

### Puis-je avoir plusieurs vérificateurs contextuels ?

Oui ! Vous pouvez créer autant de vérificateurs contextuels que nécessaire. Le système utilisera automatiquement celui qui supporte la combinaison permission/subject.

### Comment tester les permissions ?

```php
use Fasterdot\SymfonyPermissionManager\Domain\Interface\PermissionCheckerInterface;

class MyTest extends TestCase
{
    public function testUserCanEditArticle(): void
    {
        $checker = $this->getContainer()->get(PermissionCheckerInterface::class);
        
        $article = new Article();
        $article->setAuthor($this->user);
        
        $this->assertTrue($checker->can('article_edit', 'OR', $article));
    }
}
```

### Le package supporte-t-il le cache ?

L'option de cache est disponible dans la configuration mais n'est pas encore implémentée. Elle sera disponible dans une future version.

## Migration

Si vous migrez depuis une version antérieure, consultez le fichier [MIGRATION.md](MIGRATION.md).

## Changelog

Consultez le fichier [CHANGELOG.md](CHANGELOG.md) pour la liste complète des améliorations et changements.

## Contribution

Les contributions sont les bienvenues ! N'hésitez pas à ouvrir une issue ou une pull request.

## Licence

MIT

## Support

Pour toute question ou problème, ouvrez une issue sur [GitHub](https://github.com/fasterdot/symfony-permission-manager).
