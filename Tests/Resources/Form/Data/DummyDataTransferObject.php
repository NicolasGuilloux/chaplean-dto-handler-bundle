<?php declare(strict_types=1);

/*
 * This file is part of the DtoHandlerBundle package.
 *
 * (c) Chaplean.coop <contact@chaplean.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Form\Data;

use Chaplean\Bundle\DtoHandlerBundle\Annotation\DTO;
use Chaplean\Bundle\DtoHandlerBundle\Annotation\MapTo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Validator\Constraints as Assert;
use Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Entity\DummyEntity;
use Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Form\Data\SubDataTransferObject;

/**
 * Class DummyDataTransferObject
 *
 * @package   Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Form\Data
 * @author    Nicolas - Chaplean <nicolas@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 *
 * @DTO
 */
final class DummyDataTransferObject
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
     *     @Assert\Type("Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Form\Data\SubDataTransferObject")
     * )
     * @Assert\Valid
     */
    public $property7;
}
