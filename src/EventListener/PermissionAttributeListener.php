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
use Fasterdot\SymfonyPermissionManager\Helper\PermissionHelper;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class PermissionAttributeListener
{
    public function __construct(
        private PermissionHelper $permissionHelper
    ) {}

    public function __invoke(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        $refMethod = new \ReflectionMethod($controller[0], $controller[1]);
        $attributes = [...$refMethod->getAttributes(HasPermission::class)];

        $refClass = new \ReflectionClass($controller[0]);
        $attributes = [...$attributes, ...$refClass->getAttributes(HasPermission::class)];

        foreach ($attributes as $attr) {
            /** @var HasPermission $permissionAttr */
            $permissionAttr = $attr->newInstance();

            if (!$this->permissionHelper->can($permissionAttr->permission)) {
                throw new AccessDeniedHttpException('Permission refusée : ' . json_encode($permissionAttr->permission));
            }
        }
    }
}
