<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class DataTransferObjectValidationException
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\Exception
 * @author    Nicolas Guilloux <nicolas.guilloux@protonmail.com>
 */
class DataTransferObjectValidationException extends HttpException
{
    /**
     * @var ConstraintViolationListInterface
     */
    protected $violations;

    /**
     * DataTransferObjectValidationException constructor.
     *
     * @param ConstraintViolationListInterface $violations
     * @param integer|null                     $statusCode
     */
    public function __construct(ConstraintViolationListInterface $violations, int $statusCode = null)
    {
        $this->violations = $violations;

        parent::__construct($statusCode ?? Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }

    /**
     * @return array
     */
    public function getViolationsArray(): array
    {
        $errors = [];

        /** @var ConstraintViolation $violation */
        foreach ($this->violations as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }

        return $errors;
    }
}
