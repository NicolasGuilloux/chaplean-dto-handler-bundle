<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\Serializer;

use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

/**
 * Class EntityIdNormalizer
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\Serializer
 * @author    Nicolas Guilloux <nguilloux@richcongress.com>
 * @copyright 2014 - 2020 RichCongress (https://www.richcongress.com)
 */
class EntityIdNormalizer implements ContextAwareNormalizerInterface
{
    public const CONTEXT_TAG = '_entity_id_normalizer';

    /**
     * @param mixed $object
     * @param null  $format
     * @param array $context
     *
     * @return integer
     */
    public function normalize($object, $format = null, array $context = []): int
    {
        return $object->getId();
    }

    /**
     * @param mixed $data
     * @param null  $format
     * @param array $context
     *
     * @return boolean
     *
     * @throws \ReflectionException
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        $isEntityIdNormalization = $context[self::CONTEXT_TAG] ?? false;

        if (!is_object($data) || !$isEntityIdNormalization) {
            return false;
        }

        $reflectionClass = new \ReflectionClass($data);

        return $reflectionClass->hasMethod('getId');
    }
}
