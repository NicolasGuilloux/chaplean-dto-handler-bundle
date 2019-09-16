<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\Utility;

use Doctrine\Common\Collections\Collection;

/**
 * Class DtoUtility
 *
 * @package Chaplean\Bundle\DtoHandlerBundle\Utility
 * @author  Nicolas Guilloux <nicolas.guilloux@protonmail.com>
 */
class DtoUtility
{
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
}
