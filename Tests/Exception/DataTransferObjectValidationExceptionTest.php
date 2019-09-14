<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\Tests\Exception;

use Chaplean\Bundle\DtoHandlerBundle\Exception\DataTransferObjectValidationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Class DataTransferObjectValidationExceptionTest
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\Tests\Exception
 * @author    Nicolas Guilloux <nicolas.guilloux@protonmail.com>
 */
class DataTransferObjectValidationExceptionTest extends TestCase
{
    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Exception\DataTransferObjectValidationException::__construct()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Exception\DataTransferObjectValidationException::getViolations()
     * @covers \Chaplean\Bundle\DtoHandlerBundle\Exception\DataTransferObjectValidationException::getViolationsArray()
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $violation1 = new ConstraintViolation('Bad Value 1', null, [], 'badValue1', 'violation1', 'badValue1');
        $violation2 = new ConstraintViolation('Bad Value 2', null, [], 'badValue2', 'violation2', 'badValue2');

        $violations = new ConstraintViolationList(
            [
                $violation1,
                $violation2
            ]
        );

        $exception = new DataTransferObjectValidationException($violations);
        $expectedArray = [
            'violation1' => 'Bad Value 1',
            'violation2' => 'Bad Value 2',
        ];

        self::assertSame($violations, $exception->getViolations());
        self:self::assertEquals($expectedArray, $exception->getViolationsArray());
    }
}
