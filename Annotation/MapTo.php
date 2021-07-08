<?php declare(strict_types=1);

/*
 * This file is part of the DtoHandlerBundle package.
 *
 * (c) Chaplean.coop <contact@chaplean.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chaplean\Bundle\DtoHandlerBundle\Annotation;

/**
 * Class MapTo.
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\Annotation
 * @author    Nicolas - Chaplean <nicolas@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class MapTo
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
