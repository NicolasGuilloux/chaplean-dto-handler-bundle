<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\Utils;

use Doctrine\Common\Collections\Collection;

/**
 * Class EntityUtils
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\Utils
 * @author    Nicolas Guilloux <nguilloux@richcongress.com>
 * @copyright 2014 - 2020 RichCongress (https://www.richcongress.com)
 */
class EntityUtils
{
    /**
     * @param Collection         $entityList
     * @param \Traversable|array $newEntityList
     *
     * @return Collection
     */
    public static function updateCollection(Collection $entityList, $newEntityList): Collection
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
