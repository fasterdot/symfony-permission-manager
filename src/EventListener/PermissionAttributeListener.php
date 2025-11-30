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

            if (!$this->permissionChecker->can($permissionAttr->permission)) {
                throw new PermissionDeniedException($this->errorMessage);
            }
        }
    }
}
