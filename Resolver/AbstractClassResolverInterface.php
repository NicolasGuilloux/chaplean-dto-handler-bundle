<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\Resolver;

use Symfony\Component\HttpFoundation\Request;

interface AbstractClassResolverInterface
{
    public function getClass(Request $request, $value): string;

    public function supports(string $abstractClass): bool;

    public static function getPriority(): int;
}
