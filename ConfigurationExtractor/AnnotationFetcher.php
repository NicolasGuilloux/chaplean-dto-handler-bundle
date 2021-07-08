<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor;

use Doctrine\Common\Annotations\AnnotationReader;

final class AnnotationFetcher
{
    /** @var AnnotationReader */
    private static $annotationReader;

    public static function get(\Reflector $reflector, string $class)
    {
        if (method_exists($reflector, 'getAttributes')) {
            $attribute = $reflector->getAttributes($class)[0] ?? null;

            if ($attribute !== null) {
                return $attribute->newInstance();
            }
        }

        if ($reflector instanceof \ReflectionClass) {
            return self::getAnnotationReader()->getClassAnnotation($reflector, $class);
        }

        if ($reflector instanceof \ReflectionMethod) {
            return self::getAnnotationReader()->getMethodAnnotation($reflector, $class);
        }

        if ($reflector instanceof \ReflectionFunction) {
            return self::getAnnotationReader()->getFunctionAnnotation($reflector, $class);
        }

        if ($reflector instanceof \ReflectionProperty) {
            return self::getAnnotationReader()->getPropertyAnnotation($reflector, $class);
        }

        throw new \LogicException('Unsupported reflector.');
    }

    private static function getAnnotationReader(): AnnotationReader
    {
        if (self::$annotationReader === null) {
            self::$annotationReader = new AnnotationReader();
        }

        return self::$annotationReader;
    }
}
