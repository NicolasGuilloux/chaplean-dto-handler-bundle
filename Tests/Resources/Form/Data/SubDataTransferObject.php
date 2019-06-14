<?php declare(strict_types=1);

namespace Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Form\Data;

use Chaplean\Bundle\DtoHandlerBundle\Annotation\DTO;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SubDataTransferObject.
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\Tests\Resources\Form\Data
 * @author    Nicolas - Chaplean <nicolas@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 *
 * @DTO
 */
final class SubDataTransferObject
{
    /**
     * @var string
     *
     * @Assert\NotNull
     * @Assert\Type("string")
     */
    public $keyname;
}
