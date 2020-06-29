<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\Tests\Utils;

use Chaplean\Bundle\DtoHandlerBundle\Utils\EntityUtils;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Entity\DummyEntity;

class EntityUtilsTest extends TestCase
{
    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Utils\EntityUtils::updateCollection
     *
     * @return void
     */
    public function testUpdateCollectionWithArrayCollection(): void
    {
        $entity1 = new DummyEntity();
        $entity2 = new DummyEntity();
        $entity3 = new DummyEntity();

        $arrayCollection = new ArrayCollection([$entity1, $entity2]);
        $updateArrayCollection = new ArrayCollection([$entity2, $entity3]);

        $newArrayCollection = EntityUtils::updateCollection($arrayCollection, $updateArrayCollection);

        self::assertSame($newArrayCollection, $arrayCollection);
        self::assertCount(2, $arrayCollection);
        self::assertFalse($arrayCollection->contains($entity1));
        self::assertTrue($arrayCollection->contains($entity2));
        self::assertTrue($arrayCollection->contains($entity3));
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Utils\EntityUtils::updateCollection
     *
     * @return void
     */
    public function testUpdateCollectionWithArray(): void
    {
        $entity1 = new DummyEntity();
        $entity2 = new DummyEntity();
        $entity3 = new DummyEntity();

        $arrayCollection = new ArrayCollection([$entity1, $entity2]);
        $updateArrayCollection = [$entity2, $entity3];

        $newArrayCollection = EntityUtils::updateCollection($arrayCollection, $updateArrayCollection);

        self::assertSame($newArrayCollection, $arrayCollection);
        self::assertCount(2, $arrayCollection);
        self::assertFalse($arrayCollection->contains($entity1));
        self::assertTrue($arrayCollection->contains($entity2));
        self::assertTrue($arrayCollection->contains($entity3));
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Utils\EntityUtils::updateCollection
     *
     * @return void
     */
    public function testUpdateCollectionWithBadValue(): void
    {
        $entity1 = new DummyEntity();
        $entity2 = new DummyEntity();

        /** @var array $badValue */
        $badValue = '';
        $arrayCollection = new ArrayCollection([$entity1, $entity2]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The new entity list must be an array or a Collection');

        EntityUtils::updateCollection($arrayCollection, $badValue);
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Utils\EntityUtils::updateCollection
     *
     * @return void
     */
    public function testUpdateCollectionIsUntouchedWhenNewCollectionContainsTheSameElements(): void
    {
        $entity1 = new DummyEntity();
        $entity2 = new DummyEntity();
        $entity3 = new DummyEntity();

        $arrayCollection = new ArrayCollection([$entity1, $entity2, $entity3]);
        $updateArrayCollection = new ArrayCollection([$entity1, $entity2, $entity3]);

        $originalArrayCollection = clone $arrayCollection;
        $newArrayCollection = EntityUtils::updateCollection($arrayCollection, $updateArrayCollection);

        self::assertEquals($originalArrayCollection, $newArrayCollection);
    }
}
