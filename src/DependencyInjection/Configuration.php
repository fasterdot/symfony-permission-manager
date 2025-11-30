<?php

/*
 * This file is part of the SymfonyPermissionManager project.
 *
 * (c) David Tondwa <david.tondwa@hotmail.com>
 *
 */
declare(strict_types=1);

namespace Fasterdot\SymfonyPermissionManager\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration du bundle.
 * 
 * Définit la structure de configuration disponible pour le bundle.
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('fasterdot_symfony_permission_manager');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('error_message')
                    ->defaultValue('Accès refusé : permissions insuffisantes.')
                    ->info('Message d\'erreur personnalisé à afficher lors d\'un refus d\'accès.')
                ->end()
                ->booleanNode('enable_cache')
                    ->defaultValue(false)
                    ->info('Active le cache des permissions pour améliorer les performances.')
                ->end()
            ->end();

        return $treeBuilder;
    }
}

