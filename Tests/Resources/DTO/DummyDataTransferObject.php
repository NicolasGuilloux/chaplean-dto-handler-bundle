<?php declare(strict_types=1);

/*
 * This file is part of the DtoHandlerBundle package.
 *
 * (c) Chaplean.coop <contact@chaplean.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\DTO;

use Chaplean\Bundle\DtoHandlerBundle\Annotation\Field;
use Chaplean\Bundle\DtoHandlerBundle\Annotation\MapTo;
use Chaplean\Bundle\DtoHandlerBundle\DataTransferObject\DataTransferObjectInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Validator\Constraints as Assert;
use Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Entity\DummyEntity;

/**
 * Class DummyDataTransferObject
 *
 * @package   Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\DTO
 * @author    Nicolas - Chaplean <nicolas@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 */
final class DummyDataTransferObject implements DataTransferObjectInterface
{
    /**
     * @var DummyEntity
     */
    public $targetEntity;

    /**
     * @var string
     */
    public $property1;

    /**
     * @var integer
     *
     * @Assert\NotNull
     * @Assert\Type("integer")
     */
    public $property2;

    /**
     * @var DummyEntity
     *
     * @Assert\Type("Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Entity\DummyEntity")
     * @MapTo("keyname")
     */
    #[
        Assert\Type(DummyEntity::class),
        MapTo('keyname')
    ]
    public $property3;

    /**
     * @var DummyEntity
     *
     * @Assert\Type("Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Entity\DummyEntity")
     * @ParamConverter(name="property4", class="\Chaplean\Bundle\DtoHandlerBundle\Tests\Resources\Entity\DummyEntity")
     */
    public $property4;

    /**
     * @var DummyEntity
     *
     * @Assert\All(@Assert\Type("Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Entity\DummyEntity"))
     * @MapTo("keyname")
     */
    public $property5;

    /**
     * @var DummyEntity
     *
     * @Assert\All(
     *     @Assert\NotNull
     * )
     */
    public $property6;

    /**
     * @var SubDataTransferObject
     *
     * @Assert\All(
     *     @Assert\Type("Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\DTO\SubDataTransferObject")
     * )
     * @Assert\Valid
     */
    public $property7;

    /**
     * @var DummyEntity
     *
     * @Assert\NotNull
     * @Assert\DateTime
     */
    public $property8;

    /**
     * @var DummyEntity
     *
     * @Assert\Date
     */
    #[Field('another_property')]
    public $property9;

    /**
     * @var SubDataTransferObject
     *
     * @Assert\Type("Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\DTO\SubDataTransferObject")
     */
    public $property10;
}
