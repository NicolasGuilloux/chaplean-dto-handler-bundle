<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\Utility;

use Doctrine\Common\Collections\Collection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\HttpFoundation\Request;

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
     *
     * @return Collection
     */
    public static function updateEntityList(Collection $entityList, $newEntityList): Collection
    {
        if (!is_array($newEntityList) && !($newEntityList instanceof \Traversable)) {
            throw new \InvalidArgumentException('The new entity list must be an array or a Collection');
        }

        foreach ($entityList as $value) {
            $entityList->removeElement($value);
        }

        foreach ($newEntityList as $value) {
            $entityList->add($value);
        }

        return $entityList;
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
