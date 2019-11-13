<?php /** @noinspection PhpUndefinedMethodInspection */
declare(strict_types=1);

/*
 * This file is part of the DtoHandlerBundle package.
 *
 * (c) Chaplean.coop <contact@chaplean.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Chaplean\Bundle\DtoHandlerBundle\ParamConverter;

use Chaplean\Bundle\DtoHandlerBundle\Exception\DataTransferObjectValidationException;
use Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter;
use Doctrine\Common\Annotations\AnnotationException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use phpmock\mockery\PHPMockery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\DTO\SubDataTransferObject;
use Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Entity\DummyEntity;
use Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\DTO\DummyDataTransferObject;

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
     * @var TranslatorInterface|MockInterface
     */
    private $translator;

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
        $this->translator = \Mockery::mock(TranslatorInterface::class);
        $this->validator = \Mockery::mock(ValidatorInterface::class);

        PHPMockery::mock('Chaplean\Bundle\DtoHandlerBundle\ParamConverter', 'uniqid')->andReturn('hash');

        $this->dataTransferObjectParamConverter = new DataTransferObjectParamConverter(
            [\DateTime::class],
            [
                ['validation_group' => 'http_conflict_exception', 'http_status_code' => 409, 'priority' => -1],
                ['validation_group' => 'Default', 'http_status_code' => 400, 'priority' => 0],
            ],
            $this->manager,
            $this->translator,
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
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::setTaggedDtoServices()
     *
     * @return void
     *
     * @throws AnnotationException
     * @throws \ReflectionException
     */
    public function testSupportsClassWithTag(): void
    {
        $configurationSupported = new ParamConverter(
            [
                'class' => DummyDataTransferObject::class,
            ]
        );

        $this->dataTransferObjectParamConverter
            ->setTaggedDtoServices(
                [
                    DummyDataTransferObject::class => 'app.data_transfer_object'
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
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::applyParamConverters()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigure()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureProperty()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureOne()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::buildObject()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::validate()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::getValueFromRequest()
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

        $this->manager->shouldReceive('apply')->times(7);

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
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::applyParamConverters()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigure()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureProperty()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureOne()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::buildObject()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::validate()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::getValueFromRequest()
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws AnnotationException
     */
    public function testApplyWithoutValidationWithMixedSourceValues(): void
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
        $request->query->set('property2', 1);
        $request->attributes->set('property2', 1);
        $request->query->set('property3', 'test');
        $request->attributes->set('property5', ['test']);
        $request->request->set(
            'property7',
            [
                ['keyname' => 'test1'],
                ['keyname' => 'test2'],
            ]
        );

        $this->manager->shouldReceive('apply')->times(7);

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
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::applyParamConverters()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigure()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureProperty()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureOne()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::buildObject()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::validate()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::getValueFromRequest()
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

        $this->manager->shouldReceive('apply')->times(7);

        $violations = new ConstraintViolationList();

        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(
                \Mockery::type(DummyDataTransferObject::class),
                null,
                ['dto_raw_input_validation']
            )
            ->andReturn(new ConstraintViolationList());

        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(
                \Mockery::type(DummyDataTransferObject::class),
                null,
                'Default'
            )
            ->andReturn($violations);

        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(
                \Mockery::type(DummyDataTransferObject::class),
                null,
                'http_conflict_exception'
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
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::applyParamConverters()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigure()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureProperty()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureOne()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::buildObject()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::getValueFromRequest()
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

        $this->manager->shouldReceive('apply')->times(7);

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
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::applyParamConverters()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigure()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureProperty()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureOne()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::buildObject()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::validate()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::getValueFromRequest()
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

        $this->manager->shouldReceive('apply')->times(5);

        $violation = \Mockery::mock(ConstraintViolation::class);

        $violations = new ConstraintViolationList();
        $violations->add($violation);

        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(
                \Mockery::type(DummyDataTransferObject::class),
                null,
                ['dto_raw_input_validation']
            )
            ->andReturn(new ConstraintViolationList());

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
        self::assertEquals($violations, $request->attributes->get('violationErrors'));
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::apply()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::applyParamConverters()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::applyParamConverters()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigure()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureProperty()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureOne()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::buildObject()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::validate()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::getValueFromRequest()
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws AnnotationException
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

        $this->manager->shouldReceive('apply')->times(5);

        $violation = \Mockery::mock(ConstraintViolation::class);
        $violations = new ConstraintViolationList();
        $violations->add($violation);

        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(
                \Mockery::type(DummyDataTransferObject::class),
                null,
                ['dto_raw_input_validation']
            )
            ->andReturn(new ConstraintViolationList());

        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(
                \Mockery::type(DummyDataTransferObject::class),
                null,
                'Default'
            )
            ->andReturn($violations);

        self::expectException(DataTransferObjectValidationException::class);

        $this->dataTransferObjectParamConverter->apply($request, $configuration);
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::apply()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::applyParamConverters()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigure()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureProperty()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureOne()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::buildObject()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::validate()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::getValueFromRequest()
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws AnnotationException
     */
    public function testApplyWithValidationWithConflictException(): void
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

        $this->manager->shouldReceive('apply')->times(5);

        $violation = \Mockery::mock(ConstraintViolation::class);
        $violations = new ConstraintViolationList();
        $violations->add($violation);

        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(
                \Mockery::type(DummyDataTransferObject::class),
                null,
                ['dto_raw_input_validation']
            )
            ->andReturn(new ConstraintViolationList());

        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(
                \Mockery::type(DummyDataTransferObject::class),
                null,
                'Default'
            )
            ->andReturn(new ConstraintViolationList());

        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(
                \Mockery::type(DummyDataTransferObject::class),
                null,
                'http_conflict_exception'
            )
            ->andReturn($violations);

        self::expectException(DataTransferObjectValidationException::class);

        $this->dataTransferObjectParamConverter->apply($request, $configuration);
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::apply()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::applyParamConverters()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigure()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureProperty()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureOne()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::buildObject()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::getValueFromRequest()
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws AnnotationException
     */
    public function testApplyWithUnwrittableProperties(): void
    {
        $configuration = new ParamConverter(
            [
                'name'      => 'dataTransferObject',
                'class'     => SubDataTransferObject::class,
                'converter' => 'fos_rest.request_body',
            ]
        );

        $request = new Request();
        $request->request->set('keyname', 'Keyname');
        $request->request->set('unaccessible', 2);
        $request->request->set('accessible', 'Accessible');

        $request->attributes->set('dataTransferObject', null);
        $request->attributes->set(0, 'UselessAttribute');
        $request->attributes->set('parasite_', 'UselessAttribute');

        $this->manager->shouldReceive('apply')->never();

        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(
                \Mockery::type(SubDataTransferObject::class),
                null,
                ['dto_raw_input_validation']
            )
            ->andReturn(new ConstraintViolationList());

        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(
                \Mockery::type(SubDataTransferObject::class),
                null,
                'Default'
            )
            ->andReturn(new ConstraintViolationList());

        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(
                \Mockery::type(SubDataTransferObject::class),
                null,
                'http_conflict_exception'
            )
            ->andReturn(new ConstraintViolationList());

        $this->dataTransferObjectParamConverter->apply($request, $configuration);

        $expectedDto = new SubDataTransferObject();
        $expectedDto->keyname = 'Keyname';
        $expectedDto->setAccessible('Accessible');

        self::assertEquals($expectedDto, $request->attributes->get('dataTransferObject'));
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::apply()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::applyParamConverters()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::getViolationFromException()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigure()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureProperty()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::autoConfigureOne()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::buildObject()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::validate()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\ParamConverter\DataTransferObjectParamConverter::getValueFromRequest()
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws AnnotationException
     */
    public function testApplyWithManagerFailure(): void
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
        $request->request->set('property7', [
            ['keyname' => 'test1'],
            ['keyname' => 'test2']
        ]);

        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(
                \Mockery::type(DummyDataTransferObject::class),
                null,
                ['dto_raw_input_validation']
            )
            ->andReturn(new ConstraintViolationList());

        $this->manager->shouldReceive('apply')->times(4);

        $this->manager
            ->shouldReceive('apply')
            ->once()
            ->andThrow(
                new \Exception('Error')
            );

        $this->manager
            ->shouldReceive('apply')
            ->once()
            ->andThrow(
                new DataTransferObjectValidationException(new ConstraintViolationList())
            );

        $this->manager
            ->shouldReceive('apply')
            ->once()
            ->andThrow(
                new NotFoundHttpException('Error not found')
            );

        $this->translator
            ->shouldReceive('trans')
            ->once()
            ->with('dto_handler.entity_not_found', \Mockery::type('array'))
            ->andReturn('Not Found');

        self::expectException(DataTransferObjectValidationException::class);

        $this->dataTransferObjectParamConverter->apply($request, $configuration);
    }
}
