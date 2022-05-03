<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\Serializer;

use Chaplean\Bundle\DtoHandlerBundle\Annotation\Field;
use Chaplean\Bundle\DtoHandlerBundle\Annotation\MapTo;
use Chaplean\Bundle\DtoHandlerBundle\DataTransferObject\DataTransferObjectInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class DataTransferObjectNormalizer
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\Serializer
 * @author    Nicolas Guilloux <nguilloux@richcongress.com>
 * @copyright 2014 - 2020 RichCongress (https://www.richcongress.com)
 */
class DataTransferObjectNormalizer implements NormalizerInterface
{
    public const DTO_ENTITY_NORMALIZATION = 'data_transfer_object_entity_normalization';

    /**
     * @var AnnotationReader
     */
    protected $annotationReader;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @var NormalizerInterface
     */
    protected $normalizer;

    /**
     * DataTransferObjectNormalizer constructor.
     *
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->annotationReader = new AnnotationReader();
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->normalizer = $normalizer;
    }

    /**
     * @param mixed       $data
     * @param string|null $format
     * @param array       $context
     *
     * @return array
     *
     * @throws \ReflectionException
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []): array
    {
        $normalizeSubEntities = $context[self::DTO_ENTITY_NORMALIZATION] ?? true;
        $reflectionClass = new \ReflectionClass($data);
        $body = [];

        foreach ($reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            $key = $this->getKey($reflectionProperty);
            $value = $this->getValue($reflectionProperty, $data, $normalizeSubEntities);

            $body[$key] = $value;
        }

        if (!$normalizeSubEntities) {
            $context[EntityIdNormalizer::CONTEXT_TAG] = true;
        }

        return $this->normalizer->normalize($body, $format, $context);
    }

    /**
     * @param mixed $data
     * @param null  $format
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return is_object($data) && $data instanceof DataTransferObjectInterface;
    }

    /**
     * @param \ReflectionProperty $property
     *
     * @return string
     */
    protected function getKey(\ReflectionProperty $property): string
    {
        /** @var Field|null $fieldAnnotation */
        $fieldAnnotation = $this->annotationReader->getPropertyAnnotation($property, Field::class);

        return $fieldAnnotation !== null
            ? $fieldAnnotation->keyname
            : $property->getName();
    }

    /**
     * @param \ReflectionProperty $property
     * @param mixed               $data
     * @param bool                $normalizerSubEntities
     *
     * @return mixed
     */
    protected function getValue(\ReflectionProperty $property, $data, bool $normalizerSubEntities)
    {
        /** @var MapTo|null $mapToAnnotation */
        $mapToAnnotation = $this->annotationReader->getPropertyAnnotation($property, MapTo::class);
        $value = $property->getValue($data);

        if ($normalizerSubEntities || $mapToAnnotation === null) {
            return $value;
        }

        return $this->propertyAccessor->getValue(
            $value,
            $mapToAnnotation->keyname
        );
    }
}
