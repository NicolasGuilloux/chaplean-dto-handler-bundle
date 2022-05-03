<?php

declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\DependencyInjection\CompilerPass;

use Chaplean\Bundle\DtoHandlerBundle\Resolver\AbstractClassResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class AbstractClassCompilerPass implements CompilerPassInterface
{
    public const TAG = 'chaplean_dto_handler.abstract_class_resolver';

    public function process(ContainerBuilder $container)
    {
        $references = $this->getReferences($container);
        $definition = $container->getDefinition(AbstractClassResolver::class);
        $definition->setProperty('resolvers', $references);
    }

    /** @return Reference[] */
    private function getReferences(ContainerBuilder $container): array
    {
        $serviceConfigs = $container->findTaggedServiceIds(self::TAG);
        $serviceIds = array_keys($serviceConfigs);

        $references = array_map(
            static function (string $serviceId): Reference {
                return new Reference($serviceId);
            },
            $serviceIds
        );

        usort(
            $references,
            static function (Reference $left, Reference $right): int {
                $leftPriority = ((string) $left)::getPriority();
                $rightPriority = ((string) $right)::getPriority();

                if ($leftPriority === $rightPriority) {
                    return 0;
                }

                return $leftPriority > $rightPriority ? -1 : 1;
            }
        );

        return $references;
    }
}
