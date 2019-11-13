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
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Entity\DummyEntity;

/**
 * Class UniqueEntityDataTest
 *
 * @package   Tests\Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints
 * @author    Nicolas - Chaplean <nicolas@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 */
class UniqueEntityDataTest extends MockeryTestCase
{
    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints\UniqueEntityData::__construct
     *
     * @return void
     */
    public function testConstructorFailEntityClass(): void
    {
        self::expectException(MissingOptionsException::class);

        new UniqueEntityData([]);
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints\UniqueEntityData::__construct()
     *
     * @return void
     */
    public function testConstructorFailFields(): void
    {
        $options = [
            'entityClass' => 'entityClass'
        ];

        self::expectException(MissingOptionsException::class);

        new UniqueEntityData($options);
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints\UniqueEntityData::__construct()
     *
     * @return void
     */
    public function testConstructorSuccessWithoutMessage(): void
    {
        $options = [
            'entityClass' => 'entityClass',
            'fields'      => []
        ];

        $constraint = new UniqueEntityData($options);

        $this->assertSame('entityClass', $constraint->entityClass);
        $this->assertSame([], $constraint->fields);
        $this->assertSame('This value is already used.', $constraint->message);
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints\UniqueEntityData::__construct()
     *
     * @return void
     */
    public function testConstructorSuccessWithMessage(): void
    {
        $options = [
            'message'     => 'blabla',
            'entityClass' => 'entityClass',
            'fields'      => []
        ];

        $constraint = new UniqueEntityData($options);

        $this->assertSame('entityClass', $constraint->entityClass);
        $this->assertSame([], $constraint->fields);
        $this->assertSame('blabla', $constraint->message);
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints\UniqueEntityData::__construct()
     *
     * @return void
     */
    public function testConstructorSuccessWithExcept(): void
    {
        $options = [
            'except'      => 'field',
            'entityClass' => 'entityClass',
            'fields'      => []
        ];

        $constraint = new UniqueEntityData($options);

        $this->assertSame('entityClass', $constraint->entityClass);
        $this->assertSame([], $constraint->fields);
        $this->assertSame('field', $constraint->except);
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints\UniqueEntityData::getTargets()
     *
     * @return void
     */
    public function testGetTargets(): void
    {
        $options = [
            'message'     => 'blabla',
            'entityClass' => 'entityClass',
            'fields'      => []
        ];

        $constraint = new UniqueEntityData($options);

        $this->assertSame(Constraint::CLASS_CONSTRAINT, $constraint->getTargets());
    }
}
