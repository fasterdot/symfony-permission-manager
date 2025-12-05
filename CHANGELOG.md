# Changelog

## [v0.4.0] - 2025

### Permissions contextuelles
- Ajout du support des permissions contextuelles basées sur un subject
- Interface `ContextualPermissionCheckerInterface` pour créer des vérificateurs personnalisés
- Résolution automatique du subject depuis les paramètres de la méthode du contrôleur
- Support dans l'attribut `#[HasPermission]` avec le paramètre `subject`

**Exemple d'utilisation :**
```php
#[HasPermission('article_edit', subject: 'article')]
public function edit(Article $article): Response { /* ... */ }
```

## [v0.3.0] - 2025

### Logique AND/OR
- Ajout du support de la logique AND pour les permissions
- Mode OR (par défaut) : l'utilisateur doit avoir au moins une des permissions
- Mode AND : l'utilisateur doit avoir toutes les permissions listées
- Support dans l'attribut `#[HasPermission]`, la fonction Twig `has_permission()` et l'interface `PermissionCheckerInterface`

**Exemple d'utilisation :**
```php
// Mode OR (par défaut)
#[HasPermission(['user_edit', 'user_delete'])]

// Mode AND
#[HasPermission(['user_edit', 'user_delete'], mode: 'AND')]
```

## [Améliorations] - 2024

### Architecture DDD
- Refactorisation vers une architecture Domain-Driven Design (DDD)
- Séparation claire entre Domain, Application et Infrastructure
- Création d'interfaces du domaine pour découpler les dépendances

### Interfaces du domaine
- `PermissionCheckerInterface` : Interface pour la vérification des permissions
- `UserWithPermissionsInterface` : Interface pour les utilisateurs avec permissions
- `RoleWithPermissionsInterface` : Interface pour les rôles avec permissions
- `PermissionInterface` : Interface pour les permissions

### Améliorations de sécurité
- Suppression de l'exposition des permissions dans les messages d'erreur
- Création d'une exception dédiée `PermissionDeniedException`
- Messages d'erreur personnalisables via la configuration

### Configuration
- Ajout d'un système de configuration avec `Configuration.php`
- Personnalisation du message d'erreur
- Option pour activer le cache (préparé pour futures implémentations)

### Services
- Refactorisation de `PermissionHelper` vers `PermissionCheckerService`
- Services privés par défaut (bonnes pratiques Symfony)
- Alias pour l'interface du domaine

### Validation
- Validation des permissions dans l'attribut `HasPermission`
- Vérification que les permissions ne sont pas vides

### Dépendances
- Ajout de `symfony/config` et `symfony/dependency-injection` dans composer.json

