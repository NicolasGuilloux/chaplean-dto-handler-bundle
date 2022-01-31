<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\Annotation;

/**
 * Class SubKey
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\Annotation
 * @author    Nicolas Guilloux <nicolas.guilloux@protonmail.com>
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class SubKey
{
    /**
     * @Required
     * @var array
     */
    public $keynames = [];

    public function __construct($keyname)
    {
        $this->keynames = (array) ($keyname['value'] ?? $keyname);
    }
}
