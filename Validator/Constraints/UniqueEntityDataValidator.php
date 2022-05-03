<?php declare(strict_types=1);

/*
 * This file is part of the DtoHandlerBundle package.
 *
 * (c) Chaplean.coop <contact@chaplean.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class UniqueEntityDataValidator.
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints
 * @author    Nicolas - Chaplean <nicolas@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 */
class UniqueEntityDataValidator extends ConstraintValidator
{
    /**
     * @var EntityManagerInterface|null
     */
    protected $entityManager;

    /**
     * @var ExpressionLanguage
     */
    protected $expressionLanguage;

    /**
     * UniqueEntityDataValidator constructor.
     *
     * @param EntityManagerInterface|null $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager = null)
    {
        $this->entityManager = $entityManager;
        $this->expressionLanguage = new ExpressionLanguage();
    }

    /**
     * @param object                      $dto
     * @param Constraint|UniqueEntityData $constraint
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function validate($dto, Constraint $constraint): void
    {
        if ($this->entityManager === null) {
            throw new \InvalidArgumentException('No EntityManager available.');
        }

        $repository = $this->entityManager->getRepository($constraint->entityClass);
        $criteria = self::buildCriteria($repository, $dto, $constraint);

        if ($criteria === null) {
            return;
        }

        $entity = $repository->findOneBy($criteria);

        if ($entity === null) {
            return;
        }

        $exceptProperty = $constraint->except;
        $exceptEntity = null;

        if ($exceptProperty !== null) {
            $exceptEntity = $this->expressionLanguage->evaluate(
                $exceptProperty,
                ['this' => $dto]
            );
        }

        if ($exceptEntity === null || $exceptEntity !== $entity) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath($constraint->fields[0])
                ->addViolation();
        }
    }

    /**
     * @param ObjectRepository $repository
     * @param                  $dto
     * @param UniqueEntityData $constraint
     *
     * @return array|null
     */
    private static function buildCriteria($repository, $dto, UniqueEntityData $constraint): ?array
    {
        $criteria = [];

        foreach ($constraint->fields as $field) {
            $fieldValue = $dto->{$field};

            if ($fieldValue === null) {
                return null;
            }

            if (is_object($fieldValue)) {
                $criteria[$field] = $fieldValue->getId();
            } else if (\is_string($fieldValue)) {
                if ($fieldValue !== '') {
                    $criteria[$field] = $fieldValue;
                }
            } else {
                $criteria[$field] = $fieldValue;
            }
        }

        return $criteria;
    }
}
