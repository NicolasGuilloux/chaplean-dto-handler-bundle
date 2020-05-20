<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\Serializer;

use Chaplean\Bundle\DtoHandlerBundle\DataTransferObject\DataTransferObjectInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * Class DataTransferObjectNormalizer
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\Serializer
 * @author    Nicolas Guilloux <nguilloux@richcongress.com>
 * @copyright 2014 - 2020 RichCongress (https://www.richcongress.com)
 */
class DataTransferObjectNormalizer implements ContextAwareNormalizerInterface
{
    public const CONTEXT_TAG = '_dto_transformation';

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * DataTransferObjectNormalizer constructor.
     *
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
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
    public function supportsNormalization($data, $format = null, array $context = []): array
    {
        $reflectionClass = new \ReflectionClass($data);
        $body = [];

        foreach ($reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            $key = $reflectionProperty->getName();
            $value = $reflectionProperty->getValue($data);

            $body[$key] = $value;
        }

        $subContext = $context;
        $subContext[EntityIdNormalizer::class] = true;

        return $this->serializer->normalize($body, $format, $subContext);
    }

    /**
     * @param mixed       $data
     * @param string|null $format
     * @param array       $context
     *
     * @return array|bool|object
     */
    public function normalize($data, $format = null, array $context = [])
    {
        if (!($context[static::CONTEXT_TAG] ?? false)) {
            return false;
        }

        return $data instanceof DataTransferObjectInterface;
    }
}
