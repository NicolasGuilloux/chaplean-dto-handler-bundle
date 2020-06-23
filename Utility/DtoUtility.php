<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\Utility;

use Doctrine\Common\Collections\Collection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class DtoUtility
 *
 * @package Chaplean\Bundle\DtoHandlerBundle\Utility
 * @author  Nicolas Guilloux <nicolas.guilloux@protonmail.com>
 */
class DtoUtility
{
    /**
     * @var ParamConverterManager
     */
    protected $paramConverterManager;

    /**
     * DtoUtility constructor.
     *
     * @param ParamConverterManager $paramConverterManager
     */
    public function __construct(ParamConverterManager $paramConverterManager)
    {
        $this->paramConverterManager = $paramConverterManager;
    }

    /**
     * @param Collection         $entityList
     * @param \Traversable|array $newEntityList
     * @param array              $comparisonProperties
     *
     * @return Collection
     */
    public static function updateEntityList(Collection $entityList, $newEntityList, array $comparisonProperties = []): Collection
    {
        if (!is_array($newEntityList) && !($newEntityList instanceof \Traversable)) {
            throw new \InvalidArgumentException('The new entity list must be an array or a Collection');
        }

        $newEntityList = $newEntityList instanceof \Traversable ? \iterator_to_array($newEntityList) : $newEntityList;

        $extractionFunction = static function ($entity) use ($comparisonProperties) {
            return self::getProperties($entity, $comparisonProperties);
        };

        if (empty($comparisonProperties)) {
            return self::replaceEntityList($entityList, $newEntityList);
        }

        $actualEntityProperties = \array_map($extractionFunction, $entityList->toArray());
        $newEntityProperties = \array_map($extractionFunction, $newEntityList);

        foreach ($actualEntityProperties as $index => $properties) {
            if (!\in_array($properties, $newEntityProperties)) {
                $entityList->removeElement($entityList->get($index));
            }
        }

        foreach ($newEntityProperties as $index => $properties) {
            if (!in_array($properties, $actualEntityProperties)) {
                $entityList->add($newEntityList[$index]);
            }
        }

        return $entityList;
    }

    /**
     * @param Collection $entityList
     * @param array      $newEntityList
     *
     * @return Collection
     */
    protected static function replaceEntityList(Collection $entityList, array $newEntityList): Collection
    {
        foreach ($entityList as $value) {
            $entityList->removeElement($value);
        }

        foreach ($newEntityList as $value) {
            $entityList->add($value);
        }

        return $entityList;
    }

    /**
     * @param object $entity
     * @param array  $propertyNames
     *
     * @return array
     */
    protected static function getProperties($entity, array $propertyNames): array
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $properties = [];

        foreach ($propertyNames as $propertyName) {
            $properties[$propertyName] = $propertyAccessor->getValue($entity, $propertyName);
        }

        return $properties;
    }

    /**
     * @param array      $data
     * @param string     $dtoClass
     * @param array|null $options
     *
     * @return mixed
     */
    public function loadArrayToDto(array $data, string $dtoClass, array $options = null)
    {
        $request = new Request(
            [],
            $data,
            [$dtoClass => 'dto']
        );

        $config = new ParamConverter([]);
        $config->setName('dto');
        $config->setClass($dtoClass);
        $config->setIsOptional(false);
        $config->setConverter('data_transfer_object_converter');
        $config->setOptions($options);

        $this->paramConverterManager->apply($request, $config);

        return $request->get('dto');
    }
}
