<?php declare(strict_types=1);

/*
 * This file is part of the DtoHandlerBundle package.
 *
 * (c) Chaplean.coop <contact@chaplean.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Chaplean\Bundle\DtoHandlerBundle\ParamConverter;

use Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter;
use Doctrine\Common\Annotations\AnnotationException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use phpmock\mockery\PHPMockery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Entity\DummyEntity;
use Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Form\Data\DummyDataTransferObject;
use Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Form\Data\SubDataTransferObject;

/**
 * Class DataTransferObjectParamConverterTest
 *
 * @package   Tests\Chaplean\Bundle\DtoHandlerBundle\ParamConverter
 * @author    Nicolas - Chaplean <nicolas@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 */
class DataTransferObjectParamConverterTest extends MockeryTestCase
{
    /**
     * @var DataTransferObjectParamConverter
     */
    private $dataTransferObjectParamConverter;

    /**
     * @var ParamConverterManager|MockInterface
     */
    private $manager;

    /**
     * @var ValidatorInterface|MockInterface
     */
    private $validator;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->manager = \Mockery::mock(ParamConverterManager::class);
        $this->validator = \Mockery::mock(ValidatorInterface::class);

        PHPMockery::mock('Chaplean\Bundle\DtoHandlerBundle\ParamConverter', 'uniqid')->andReturn('hash');

        $this->dataTransferObjectParamConverter = new DataTransferObjectParamConverter(
            $this->manager,
            $this->validator
        );
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::__construct()
     *
     * @return void
     */
    public function testConstructor(): void
    {
        self::assertInstanceOf(DataTransferObjectParamConverter::class, $this->dataTransferObjectParamConverter);
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::supports()
     *
     * @return void
     *
     * @throws AnnotationException
     * @throws \ReflectionException
     */
    public function testSupportsClassDto(): void
    {
        $configurationSupported = new ParamConverter(
            [
                'class' => DummyDataTransferObject::class,
            ]
        );

        self::assertTrue($this->dataTransferObjectParamConverter->supports($configurationSupported));
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::supports()
     *
     * @return void
     *
     * @throws AnnotationException
     * @throws \ReflectionException
     */
    public function testSupportsClassNotDto(): void
    {
        $configurationUnsupported = new ParamConverter(
            [
                'class' => DummyEntity::class,
            ]
        );

        self::assertFalse($this->dataTransferObjectParamConverter->supports($configurationUnsupported));
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::supports()
     *
     * @return void
     *
     * @throws AnnotationException
     * @throws \ReflectionException
     */
    public function testSupportsNotClass(): void
    {
        $configurationUnsupported = new ParamConverter([]);

        self::assertFalse($this->dataTransferObjectParamConverter->supports($configurationUnsupported));
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::apply()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigure()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureProperty()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureOne()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::buildObject()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::validate()
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws AnnotationException
     */
    public function testApplyWithoutValidation(): void
    {
        $configuration = new ParamConverter(
            [
                'name'      => 'dataTransferObject',
                'class'     => DummyDataTransferObject::class,
                'converter' => 'fos_rest.request_body',
                'options'   => ['validate' => false],
            ]
        );

        $request = new Request();
        $request->request->set('property1', 'Property 1');
        $request->request->set('property2', 2);
        $request->request->set('property3', 'test');
        $request->request->set('property5', ['test']);
        $request->request->set(
            'property7',
            [
                ['keyname' => 'test1'],
                ['keyname' => 'test2'],
            ]
        );

        $this->manager->shouldReceive('apply')->once();

        $this->dataTransferObjectParamConverter->apply($request, $configuration);

        $expectedDto = new DummyDataTransferObject();
        $expectedDto->property1 = 'Property 1';
        $expectedDto->property2 = 2;
        $expectedDto->property3 = 'test';
        $expectedDto->property5 = ['test'];
        $expectedDto->property7 = [
            ['keyname' => 'test1'],
            ['keyname' => 'test2'],
        ];

        self::assertEquals($expectedDto, $request->attributes->get('dataTransferObject'));
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::apply()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigure()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureProperty()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureOne()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::buildObject()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::validate()
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws AnnotationException
     */
    public function testApplyWithValidationSuccess(): void
    {
        $configuration = new ParamConverter(
            [
                'name'      => 'dataTransferObject',
                'class'     => DummyDataTransferObject::class,
                'converter' => 'fos_rest.request_body',
            ]
        );

        $entity = new DummyEntity();

        $request = new Request();
        $request->request->set('property1', 'Property 1');
        $request->request->set('property2', 2);
        $request->request->set('property3', $entity);
        $request->request->set('property5', [$entity]);
        $request->request->set(
            'property7',
            [
                ['keyname' => 'test1'],
                ['keyname' => 'test2'],
            ]
        );

        $request->attributes->set(0, 'UselessAttribute');
        $request->attributes->set('parasite_', 'UselessAttribute');

        $this->manager->shouldReceive('apply')->once();

        $violations = new ConstraintViolationList();

        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(
                \Mockery::type(DummyDataTransferObject::class),
                null,
                null
            )
            ->andReturn($violations);

        $this->dataTransferObjectParamConverter->apply($request, $configuration);

        $expectedDto = new DummyDataTransferObject();
        $expectedDto->property1 = 'Property 1';
        $expectedDto->property2 = 2;
        $expectedDto->property3 = $entity;
        $expectedDto->property5 = [$entity];
        $expectedDto->property7 = [
                ['keyname' => 'test1'],
                ['keyname' => 'test2'],
        ];

        self::assertEquals($expectedDto, $request->attributes->get('dataTransferObject'));
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::apply()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigure()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureProperty()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureOne()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::buildObject()
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws AnnotationException
     */
    public function testApplySubDto(): void
    {
        $configuration = new ParamConverter(
            [
                'name'      => 'dataTransferObject',
                'class'     => DummyDataTransferObject::class,
                'converter' => 'fos_rest.request_body',
            ]
        );

        $entity = new DummyEntity();

        $request = new Request();
        $request->request->set('property1', 'Property 1');
        $request->request->set('property2', 2);
        $request->request->set('property3', $entity);
        $request->request->set('property5', [$entity]);

        $request->attributes->set('dataTransferObject', [
            'property1' => 'Property 1',
            'property2' => 2,
            'property3' => $entity,
            'property5' => [$entity],
            'property7' => [
                ['keyname' => 'test1'],
                ['keyname' => 'test2']
            ]
        ]);
        $request->attributes->set(0, 'UselessAttribute');
        $request->attributes->set('parasite_', 'UselessAttribute');

        $this->manager->shouldReceive('apply')->once();

        $this->validator->shouldNotReceive('validate');

        $this->dataTransferObjectParamConverter->apply($request, $configuration);

        $expectedDto = new DummyDataTransferObject();
        $expectedDto->property1 = 'Property 1';
        $expectedDto->property2 = 2;
        $expectedDto->property3 = $entity;
        $expectedDto->property5 = [$entity];
        $expectedDto->property7 = [
            ['keyname' => 'test1'],
            ['keyname' => 'test2'],
        ];

        self::assertEquals($expectedDto, $request->attributes->get('dataTransferObject'));
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::apply()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigure()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureProperty()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureOne()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::buildObject()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::validate()
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws AnnotationException
     */
    public function testApplyWithValidationWithHandlerWithValidationGroup(): void
    {
        $configuration = new ParamConverter(
            [
                'name'      => 'dataTransferObject',
                'class'     => DummyDataTransferObject::class,
                'converter' => 'fos_rest.request_body',
                'options'   => [
                    'violations' => 'violationErrors',
                    'groups'     => ['validation_group'],
                ],
            ]
        );

        $entity = new DummyEntity();

        $request = new Request();
        $request->request->set('property1', 'Property 1');
        $request->request->set('property2', 2);
        $request->request->set('property3', $entity);
        $request->request->set('property5', [$entity]);

        $this->manager->shouldReceive('apply')->once();

        $violation = \Mockery::mock(ConstraintViolation::class);

        $violations = new ConstraintViolationList();
        $violations->add($violation);

        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(
                \Mockery::type(DummyDataTransferObject::class),
                null,
                ['validation_group']
            )
            ->andReturn($violations);

        $this->dataTransferObjectParamConverter->apply($request, $configuration);

        $expectedDto = new DummyDataTransferObject();
        $expectedDto->property1 = 'Property 1';
        $expectedDto->property2 = 2;
        $expectedDto->property3 = $entity;
        $expectedDto->property5 = [$entity];

        self::assertEquals($expectedDto, $request->attributes->get('dataTransferObject'));
        self::assertSame($violations, $request->attributes->get('violationErrors'));
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::apply()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigure()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureProperty()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureOne()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::buildObject()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::validate()
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws AnnotationException
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessage {"Property Path":"ERROR"}
     */
    public function testApplyWithValidationWithoutHandler(): void
    {
        $configuration = new ParamConverter(
            [
                'name'      => 'dataTransferObject',
                'class'     => DummyDataTransferObject::class,
                'converter' => 'fos_rest.request_body',
            ]
        );

        $entity = new DummyEntity();

        $request = new Request();
        $request->request->set('property1', 'Property 1');
        $request->request->set('property2', 2);
        $request->request->set('property3', $entity);
        $request->request->set('property5', [$entity]);

        $this->manager->shouldReceive('apply')->once();

        $violation = \Mockery::mock(ConstraintViolation::class);
        $violation->shouldReceive('getPropertyPath')
            ->once()
            ->andReturn('Property Path');
        $violation->shouldReceive('getMessage')
            ->once()
            ->andReturn('ERROR');

        $violations = new ConstraintViolationList();
        $violations->add($violation);

        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(
                \Mockery::type(DummyDataTransferObject::class),
                null,
                null
            )
            ->andReturn($violations);

        $this->dataTransferObjectParamConverter->apply($request, $configuration);
    }
}
