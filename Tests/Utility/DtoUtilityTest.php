<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\Tests\Utility;

use Chaplean\Bundle\DtoHandlerBundle\Utility\DtoUtility;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\HttpFoundation\Request;
use Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\DTO\SubDataTransferObject;
use Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Entity\DummyEntity;

/**
 * Class DtoUtilityTest
 *
 * @package Chaplean\Bundle\DtoHandlerBundle\Tests\Utility
 * @author  Nicolas Guilloux <nicolas.guilloux@protonmail.com>
 */
class DtoUtilityTest extends MockeryTestCase
{
    /**
     * @var DtoUtility
     */
    protected $dtoUtility;

    /**
     * @var ParamConverterManager|MockInterface
     */
    protected $paramConverterManager;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->paramConverterManager = \Mockery::mock(ParamConverterManager::class);
        $this->dtoUtility = new DtoUtility($this->paramConverterManager);
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Utility\DtoUtility::updateEntityList()
     *
     * @return void
     */
    public function testUpdateEntityListWithArrayCollection(): void
    {
        $entity1 = new DummyEntity();
        $entity2 = new DummyEntity();
        $entity3 = new DummyEntity();

        $arrayCollection = new ArrayCollection([$entity1, $entity2]);
        $updateArrayCollection = new ArrayCollection([$entity2, $entity3]);

        $newArrayCollection = DtoUtility::updateEntityList($arrayCollection, $updateArrayCollection);

        self::assertSame($newArrayCollection, $arrayCollection);
        self::assertFalse($arrayCollection->contains($entity1));
        self::assertTrue($arrayCollection->contains($entity2));
        self::assertTrue($arrayCollection->contains($entity3));
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Utility\DtoUtility::updateEntityList()
     *
     * @return void
     */
    public function testUpdateEntityListWithArray(): void
    {
        $entity1 = new DummyEntity();
        $entity2 = new DummyEntity();
        $entity3 = new DummyEntity();

        $arrayCollection = new ArrayCollection([$entity1, $entity2]);
        $updateArrayCollection = [$entity2, $entity3];

        $newArrayCollection = DtoUtility::updateEntityList($arrayCollection, $updateArrayCollection);

        self::assertSame($newArrayCollection, $arrayCollection);
        self::assertFalse($arrayCollection->contains($entity1));
        self::assertTrue($arrayCollection->contains($entity2));
        self::assertTrue($arrayCollection->contains($entity3));
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Utility\DtoUtility::updateEntityList()
     *
     * @return void
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The new entity list must be an array or a Collection
     */
    public function testUpdateEntityListWithBadValue(): void
    {
        $entity1 = new DummyEntity();
        $entity2 = new DummyEntity();

        $arrayCollection = new ArrayCollection([$entity1, $entity2]);

        DtoUtility::updateEntityList($arrayCollection, '');
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Utility\DtoUtility::__construct()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Utility\DtoUtility::loadArrayToDto()
     *
     * @return void
     */
    public function testLoadArrayToDto(): void
    {
        $this->paramConverterManager
            ->shouldReceive('apply')
            ->once()
            ->with(
                \Mockery::type(Request::class),
                \Mockery::type('array')
            )
            ->andReturn(new SubDataTransferObject());

        $result = $this->dtoUtility->loadArrayToDto(['keyname' => 'perfect_value'], SubDataTransferObject::class);

        self::assertNull($result);
    }
}
