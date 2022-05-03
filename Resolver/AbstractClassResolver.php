<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\Resolver;

use Symfony\Component\HttpFoundation\Request;

class AbstractClassResolver
{
    /** @var AbstractClassResolverInterface[] */
    public $resolvers = [];

    public function getClassFor(string $abstractClass, Request $request, $value): ?string
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($abstractClass)) {
                return $resolver->getClass($request, $value);
            }
        }

        return null;
    }
}
