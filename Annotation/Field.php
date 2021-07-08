<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\Annotation;

/**
 * Class Field
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\Annotation
 * @author    Nicolas Guilloux <nicolas.guilloux@protonmail.com>
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Field
{
    /**
     * @Required
     * @var string
     */
    public $keyname;

    public function __construct($keyname)
    {
        $this->keyname = $keyname['value'] ?? $keyname;
    }
}
