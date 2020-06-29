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
     * Modify $entityList to contain only elements of $newEntityList while reusing as much of the original list as possible.
     *
     * By default, this function uses `spl_object_hash` to get a unique id per object to compare old and new elements.
     * The caller can provide a callback $getId to change how the unique id is computed.
     *
     * @param Collection         $entityList
     * @param \Traversable|array $newEntityList
     * @param callable|null      $getId
     *
     * @return Collection
     */
    public static function updateCollection(Collection $entityList, $newEntityList, ?callable $getId = null): Collection
    {
        if (!is_array($newEntityList) && !($newEntityList instanceof \Traversable)) {
            throw new \InvalidArgumentException('The new entity list must be an array or a Collection');
        }

        $getId = $getId ?? function($e) { return spl_object_hash($e); };
        $mapKeyFromValue = function ($f, $list) {
            $result = [];

            foreach ($list as $elem) {
                $result[$f($elem)] = $elem;
            }

            return $result;
        };

        $entityListWithId = $mapKeyFromValue($getId, $entityList);
        $newEntityListWithId = $mapKeyFromValue($getId, $newEntityList);

        foreach ($entityListWithId as $id => $value) {
            if (!\array_key_exists($id, $newEntityListWithId)) {
                $entityList->removeElement($value);
            } else {
                unset($newEntityListWithId[$id]);
            }
        }

        foreach ($newEntityListWithId as $id => $value) {
            $entityList->add($value);
        }

        return $entityList;
    }
}
