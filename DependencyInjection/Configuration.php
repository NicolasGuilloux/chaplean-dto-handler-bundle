<?php

/*
 * This file is part of the DtoHandlerBundle package.
 *
 * (c) Chaplean.coop <contact@chaplean.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chaplean\Bundle\DtoHandlerBundle\DependencyInjection;

use Chaplean\Bundle\DtoHandlerBundle\Exception\DataTransferObjectValidationException;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * This class contains the configuration information for the bundle.
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('chaplean_dto_handler');

        $rootNode
            ->children()
                ->arrayNode('bypass_param_converter_exception')
                    ->info('Bypass the ParamConverter exception for specified classes')
                    ->defaultValue([
                        \DateTime::class
                    ])
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('http_code_validation_groups')
                    ->info('Validate DTO with the the group and throw a HTTP exception with the mentioned status code in case of violations')
                    ->defaultValue([
                        'validation_group' => 'Default',
                        'http_code'        => Response::HTTP_BAD_REQUEST,
                    ])
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('validation_group')->end()
                            ->integerNode('http_code')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
