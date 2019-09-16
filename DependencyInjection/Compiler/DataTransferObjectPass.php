<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\DependencyInjection\Compiler;

use Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class DataTransferObjectPass
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\DependencyInjection\Compiler
 * @author    Nicolas Guilloux <nicolas.guilloux@protonmail.com>
 */
class DataTransferObjectPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(DataTransferObjectParamConverter::class)) {
            return;
        }

        $definition = $container->findDefinition(DataTransferObjectParamConverter::class);
        $taggedServices = $container->findTaggedServiceIds('app.data_transfer_object');

        $definition->addMethodCall('setTaggedDtoServices', [$taggedServices]);
    }
}
