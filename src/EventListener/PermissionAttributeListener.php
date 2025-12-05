<?php

/*
 * This file is part of the SymfonyPermissionManager project.
 *
 * (c) David Tondwa <david.tondwa@hotmail.com>
 *
 */
declare(strict_types=1);

namespace Fasterdot\SymfonyPermissionManager\EventListener;

use Fasterdot\SymfonyPermissionManager\Attribute\HasPermission;
use Fasterdot\SymfonyPermissionManager\Domain\Exception\PermissionDeniedException;
use Fasterdot\SymfonyPermissionManager\Domain\Interface\PermissionCheckerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

final readonly class PermissionAttributeListener
{
    public function __construct(
        private PermissionCheckerInterface $permissionChecker,
        private string $errorMessage = 'Accès refusé : permissions insuffisantes.'
    ) {}

    /**
     * @throws ReflectionException
     */
    public function __invoke(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (is_array($controller)) {
            [$controllerInstance, $method] = $controller;
            $refMethod = new ReflectionMethod($controllerInstance, $method);
            $refClass = new ReflectionClass($controllerInstance);
        } elseif (is_object($controller)) { 
            // Cas d'un contrôleur invocable v2
            $refMethod = new ReflectionMethod($controller, '__invoke');
            $refClass = new ReflectionClass($controller);
        } else {
            return;
        }

        $attributes = [...$refMethod->getAttributes(HasPermission::class), ...$refClass->getAttributes(HasPermission::class)];

        foreach ($attributes as $attr) {
            /** @var HasPermission $permissionAttr */
            $permissionAttr = $attr->newInstance();

            // Résout le subject si spécifié
            $subject = null;
            if ($permissionAttr->subject !== null) {
                $subject = $this->resolveSubject($refMethod, $event, $permissionAttr->subject);
            }

            if (!$this->permissionChecker->can($permissionAttr->permission, $permissionAttr->mode, $subject)) {
                throw new PermissionDeniedException($this->errorMessage);
            }
        }
    }

    /**
     * Résout le subject depuis les arguments de la méthode du contrôleur.
     *
     * @param ReflectionMethod $refMethod La méthode du contrôleur.
     * @param ControllerEvent $event L'événement du contrôleur.
     * @param string $subjectName Le nom du paramètre à résoudre.
     * @return mixed Le subject résolu ou null si non trouvé.
     */
    private function resolveSubject(ReflectionMethod $refMethod, ControllerEvent $event, string $subjectName): mixed
    {
        $request = $event->getRequest();

        // Essaie de récupérer depuis les attributs de la requête (résolus par l'argument resolver de Symfony)
        if ($request->attributes->has($subjectName)) {
            return $request->attributes->get($subjectName);
        }

        // Essaie de trouver dans les paramètres de la méthode
        $parameters = $refMethod->getParameters();
        foreach ($parameters as $index => $parameter) {
            if ($parameter->getName() === $subjectName) {
                // Essaie depuis les attributs de la requête avec le type de classe
                $type = $parameter->getType();
                if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                    $typeName = $type->getName();
                    // Symfony stocke souvent les entités résolues avec leur nom de classe complet
                    // ou avec le nom du paramètre
                    if ($request->attributes->has($typeName)) {
                        return $request->attributes->get($typeName);
                    }
                    
                    // Essaie aussi avec le nom court de la classe
                    $shortTypeName = basename(str_replace('\\', '/', $typeName));
                    if ($request->attributes->has($shortTypeName)) {
                        return $request->attributes->get($shortTypeName);
                    }
                }
            }
        }

        return null;
    }
    }
}
