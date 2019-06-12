<?php declare(strict_types=1);

/*
 * This file is part of the DtoHandlerBundle package.
 *
 * (c) Chaplean.coop <contact@chaplean.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints;

use Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints\UniqueEntityData;
use Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints\UniqueEntityDataValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Entity\DummyEntity;
use Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Form\Data\DummyDataTransferObject;

/**
 * Class UniqueEntityDataValidatorTest
 *
 * @package   Tests\Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints
 * @author    Nicolas - Chaplean <nicolas@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 */
class UniqueEntityDataValidatorTest extends MockeryTestCase
{
    /**
     * @var ExecutionContext|MockInterface
     */
    protected $context;

    /**
     * @var EntityManagerInterface|MockInterface
     */
    protected $em;

    /**
     * @var UniqueEntityDataValidator
     */
    protected $validator;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->context = \Mockery::mock(ExecutionContext::class);
        $this->em = \Mockery::mock(EntityManagerInterface::class);

        $this->validator = new UniqueEntityDataValidator($this->em);
        $this->validator->initialize($this->context);
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints\UniqueEntityDataValidator::__construct()
     *
     * @return void
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(UniqueEntityDataValidator::class, $this->validator);
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints\UniqueEntityDataValidator::__construct()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints\UniqueEntityDataValidator::validate()
     *
     * @return void
     */
    public function testWithoutEntityManager(): void
    {
        $dto = new DummyDataTransferObject();
        $constraint = new UniqueEntityData(
            [
                'entityClass' => DummyEntity::class,
                'fields'      => ['property1', 'property2']
            ]
        );

        $validator = new UniqueEntityDataValidator();
        $validator->initialize($this->context);

        $this->context->shouldNotReceive('buildViolation');

        $validator->validate($dto, $constraint);
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints\UniqueEntityDataValidator::validate()
     *
     * @return void
     */
    public function testValidateNotUnique(): void
    {
        $dto = new DummyDataTransferObject();
        $dto->property1 = 'Property 1';
        $dto->property2 = 1;

        $constraint = new UniqueEntityData(
            [
                'entityClass' => DummyEntity::class,
                'fields'      => ['property1', 'property2']
            ]
        );

        $repository = \Mockery::mock(EntityRepository::class);
        $repository->shouldReceive('findOneBy')
            ->once()
            ->with(['property1' => 'Property 1', 'property2' => 1])
            ->andReturn(new DummyEntity());

        $this->em->shouldReceive('getRepository')
            ->once()
            ->with(DummyEntity::class)
            ->andReturn($repository);

        $violation = \Mockery::mock(ConstraintViolationBuilderInterface::class);
        $violation->shouldReceive('atPath')
            ->once()
            ->with('property1')
            ->andReturnSelf();
        $violation->shouldReceive('addViolation')->once();

        $this->context->shouldReceive('buildViolation')
            ->with('This value is already used.')
            ->once()
            ->andReturn($violation);

        $this->validator->validate($dto, $constraint);
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints\UniqueEntityDataValidator::validate()
     *
     * @return void
     */
    public function testValidateNotUniqueWithExcept(): void
    {
        $entity = \Mockery::mock(DummyEntity::class);
        $entity->shouldReceive('getId')
            ->twice()
            ->andReturn(1);

        $dto = new DummyDataTransferObject();
        $dto->targetEntity = $entity;
        $dto->property1 = 'Property 1';
        $dto->property2 = 1;

        $constraint = new UniqueEntityData(
            [
                'entityClass' => DummyEntity::class,
                'fields'      => ['property1', 'property2'],
                'except'      => 'targetEntity',
            ]
        );

        $repository = \Mockery::mock(EntityRepository::class);
        $repository->shouldReceive('findOneBy')
            ->once()
            ->with(['property1' => 'Property 1', 'property2' => 1])
            ->andReturn($entity);

        $this->em->shouldReceive('getRepository')
            ->once()
            ->with(DummyEntity::class)
            ->andReturn($repository);

        $this->context->shouldNotReceive('buildViolation');

        $this->validator->validate($dto, $constraint);
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints\UniqueEntityDataValidator::validate()
     *
     * @return void
     */
    public function testValidateFieldNull(): void
    {
        $dto = new DummyDataTransferObject();
        $dto->property1 = 'Property 1';

        $constraint = new UniqueEntityData(
            [
                'entityClass' => DummyEntity::class,
                'fields'      => ['property1', 'property2']
            ]
        );

        $repository = \Mockery::mock(EntityRepository::class);
        $repository->shouldNotReceive('findOneBy');

        $this->em->shouldReceive('getRepository')
            ->once()
            ->with(DummyEntity::class)
            ->andReturn($repository);

        $this->validator->validate($dto, $constraint);
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints\UniqueEntityDataValidator::validate()
     *
     * @return void
     */
    public function testValidateFieldObject(): void
    {
        $entity = \Mockery::mock(DummyEntity::class);
        $entity->shouldReceive('getId')
            ->once()
            ->andReturn(1);

        $dto = new DummyDataTransferObject();
        $dto->property1 = 'Property 1';
        $dto->property3 = $entity;

        $constraint = new UniqueEntityData(
            [
                'entityClass' => DummyEntity::class,
                'fields'      => ['property1', 'property3']
            ]
        );

        $repository = \Mockery::mock(EntityRepository::class);
        $repository->shouldReceive('findOneBy')
            ->once()
            ->with(['property3' => 1, 'property1' => 'Property 1'])
            ->andReturnNull();

        $this->em
            ->shouldReceive('getRepository')
            ->once()
            ->with(DummyEntity::class)
            ->andReturn($repository);

        $this->validator->validate($dto, $constraint);
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints\UniqueEntityDataValidator::validate()
     *
     * @return void
     */
    public function testValidateUnique(): void
    {
        $dto = new DummyDataTransferObject();
        $dto->property1 = 'Property 1';
        $dto->property2 = 1;

        $constraint = new UniqueEntityData(
            [
                'entityClass' => DummyEntity::class,
                'fields'      => ['property1', 'property2']
            ]
        );

        $repository = \Mockery::mock(EntityRepository::class);
        $repository->shouldReceive('findOneBy')
            ->once()
            ->with(['property1' => 'Property 1', 'property2' => 1])
            ->andReturnNull();

        $this->em->shouldReceive('getRepository')
            ->once()
            ->with(DummyEntity::class)
            ->andReturn($repository);

        $this->validator->validate($dto, $constraint);
    }
}
