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
     * When there is an id match between $entityList and $newEntityList items, the default is to keep the original item untouched.
     * The caller can provide a callback $merge to allow updating the kept value with the matched new value.
     *
     * @param Collection         $entityList
     * @param \Traversable|array $newEntityList
     * @param callable|null      $getId
     * @param callable|null      $merge
     *
     * @return Collection
     */
    public static function updateCollection(
        Collection $entityList,
        $newEntityList,
        ?callable $getId = null,
        ?callable $merge = null
    ): Collection
    {
        if (!is_array($newEntityList) && !($newEntityList instanceof \Traversable)) {
            throw new \InvalidArgumentException('The new entity list must be an array or a Collection');
        }

        $getId = $getId ?? function($e) { return spl_object_hash($e); };
        $newEntityListWithId = [];

        foreach ($newEntityList as $elem) {
            $newEntityListWithId[$getId($elem)] = $elem;
        }

        foreach ($entityList as $id => $value) {
            $valueId = $getId($value);

            if (!\array_key_exists($valueId, $newEntityListWithId)) {
                $entityList->removeElement($value);
            } else {
                $entity = $entityList->get($id);
                if ($merge !== null) {
                    $merge($entity, $newEntityListWithId[$valueId]);
                }

                unset($newEntityListWithId[$valueId]);
            }
        }

        foreach ($newEntityListWithId as $id => $value) {
            $entityList->add($value);
        }

        return $entityList;
    }
}
