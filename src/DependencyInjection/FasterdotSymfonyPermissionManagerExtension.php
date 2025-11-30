<?php

/*
 * This file is part of the SymfonyPermissionManager project.
 *
 * (c) David Tondwa <david.tondwa@hotmail.com>
 *
 */
namespace Fasterdot\SymfonyPermissionManager\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class FasterdotSymfonyPermissionManagerExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Enregistre la configuration dans les paramètres du conteneur
        $container->setParameter('fasterdot_symfony_permission_manager.error_message', $config['error_message']);
        $container->setParameter('fasterdot_symfony_permission_manager.enable_cache', $config['enable_cache']);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(realpath(__DIR__ . '/../Resources/config'))
        );
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        // Préparation de la configuration si nécessaire
    }
}

