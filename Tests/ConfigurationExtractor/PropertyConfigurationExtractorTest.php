<?php declare(strict_types=1);

namespace Tests\Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor;

use Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor;
use Doctrine\Common\Annotations\AnnotationException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Entity\DummyEntity;
use Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Form\Data\DummyDataTransferObject;

/**
 * Class PropertyConfigurationExtractorTest
 *
 * @package   Tests\Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor
 * @author    Nicolas - Chaplean <nicolas@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 */
class PropertyConfigurationExtractorTest extends MockeryTestCase
{
    /**
     * @var \ReflectionClass
     */
    private $dtoReflectionClass;

    /**
     * @return void
     *
     * @throws \ReflectionException
     */
    public function setUp(): void
    {
        $this->dtoReflectionClass = new \ReflectionClass(DummyDataTransferObject::class);
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::__construct()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getName()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getMapTo()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getType()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getParamConverterAnnotation()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::isOptional()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::isCollection()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::findTypeConstraint()
     *
     * @return void
     *
     * @throws AnnotationException
     * @throws \ReflectionException
     */
    public function testStringProperty(): void
    {
        $property = $this->dtoReflectionClass->getProperty('property1');
        $propertyConfigurationModel = new PropertyConfigurationExtractor($property);

        self::assertSame('property1', $propertyConfigurationModel->getName());
        self::assertNull($propertyConfigurationModel->getMapTo());
        self::assertNull($propertyConfigurationModel->getType());
        self::assertNull($propertyConfigurationModel->getParamConverterAnnotation());
        self::assertTrue($propertyConfigurationModel->isOptional());
        self::assertFalse($propertyConfigurationModel->isCollection());
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::__construct()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getName()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getMapTo()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getType()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getParamConverterAnnotation()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::isOptional()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::isCollection()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::findTypeConstraint()
     *
     * @return void
     *
     * @throws AnnotationException
     * @throws \ReflectionException
     */
    public function testIntegerNotNullProperty(): void
    {
        $property = $this->dtoReflectionClass->getProperty('property2');
        $propertyConfigurationModel = new PropertyConfigurationExtractor($property);

        self::assertSame('property2', $propertyConfigurationModel->getName());
        self::assertNull($propertyConfigurationModel->getMapTo());
        self::assertNull($propertyConfigurationModel->getType());
        self::assertNull($propertyConfigurationModel->getParamConverterAnnotation());
        self::assertFalse($propertyConfigurationModel->isOptional());
        self::assertFalse($propertyConfigurationModel->isCollection());
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::__construct()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getName()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getMapTo()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getType()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getParamConverterAnnotation()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::isOptional()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::isCollection()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::findTypeConstraint()
     *
     * @return void
     *
     * @throws AnnotationException
     * @throws \ReflectionException
     */
    public function testDummyEntityWithKeyname(): void
    {
        $property = $this->dtoReflectionClass->getProperty('property3');
        $propertyConfigurationModel = new PropertyConfigurationExtractor($property);

        self::assertSame('property3', $propertyConfigurationModel->getName());
        self::assertSame('keyname', $propertyConfigurationModel->getMapTo());
        self::assertSame(DummyEntity::class, $propertyConfigurationModel->getType());
        self::assertNull($propertyConfigurationModel->getParamConverterAnnotation());
        self::assertTrue($propertyConfigurationModel->isOptional());
        self::assertFalse($propertyConfigurationModel->isCollection());
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::__construct()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getName()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getMapTo()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getType()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getParamConverterAnnotation()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::isOptional()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::isCollection()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::findTypeConstraint()
     *
     * @return void
     *
     * @throws AnnotationException
     * @throws \ReflectionException
     */
    public function testDummyEntityWithParamConverter(): void
    {
        $property = $this->dtoReflectionClass->getProperty('property4');
        $propertyConfigurationModel = new PropertyConfigurationExtractor($property);

        self::assertSame('property4', $propertyConfigurationModel->getName());
        self::assertNull($propertyConfigurationModel->getMapTo());
        self::assertSame(DummyEntity::class, $propertyConfigurationModel->getType());
        self::assertInstanceOf(ParamConverter::class, $propertyConfigurationModel->getParamConverterAnnotation());
        self::assertTrue($propertyConfigurationModel->isOptional());
        self::assertFalse($propertyConfigurationModel->isCollection());
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::__construct()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getName()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getMapTo()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getType()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getParamConverterAnnotation()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::isOptional()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::isCollection()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::findTypeConstraint()
     *
     * @return void
     *
     * @throws AnnotationException
     * @throws \ReflectionException
     */
    public function testDummyEntityWithCollection(): void
    {
        $property = $this->dtoReflectionClass->getProperty('property5');
        $propertyConfigurationModel = new PropertyConfigurationExtractor($property);

        self::assertSame('property5', $propertyConfigurationModel->getName());
        self::assertSame('keyname', $propertyConfigurationModel->getMapTo());
        self::assertSame(DummyEntity::class, $propertyConfigurationModel->getType());
        self::assertNull($propertyConfigurationModel->getParamConverterAnnotation());
        self::assertTrue($propertyConfigurationModel->isOptional());
        self::assertTrue($propertyConfigurationModel->isCollection());
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::__construct()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getName()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getMapTo()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getType()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::getParamConverterAnnotation()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::isOptional()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::isCollection()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor::findTypeConstraint()
     *
     * @return void
     *
     * @throws AnnotationException
     * @throws \ReflectionException
     */
    public function testDummyEntityWithCollectionConstraintWithoutEntity(): void
    {
        $property = $this->dtoReflectionClass->getProperty('property6');
        $propertyConfigurationModel = new PropertyConfigurationExtractor($property);

        self::assertSame('property6', $propertyConfigurationModel->getName());
        self::assertNull($propertyConfigurationModel->getMapTo());
        self::assertNull($propertyConfigurationModel->getType());
        self::assertNull($propertyConfigurationModel->getParamConverterAnnotation());
        self::assertTrue($propertyConfigurationModel->isOptional());
        self::assertTrue($propertyConfigurationModel->isCollection());
    }
}
