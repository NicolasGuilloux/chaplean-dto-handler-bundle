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
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * UniqueEntityDataValidator constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param object                      $dto
     * @param Constraint|UniqueEntityData $constraint
     *
     * @return void
     */
    public function validate($dto, Constraint $constraint): void
    {
        $repository = $this->em->getRepository($constraint->entityClass);
        $criteria = [];

        foreach ($constraint->fields as $field) {
            $fieldValue = $dto->{$field};

            if ($fieldValue === null) {
                return;
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

        $entity = $repository->findOneBy($criteria);
        $exceptProperty = $constraint->except;
        $exceptEntity = null;

        if ($exceptProperty !== null && property_exists(get_class($dto), $exceptProperty)) {
            $exceptEntity = $dto->$exceptProperty;
        }

        if ($entity !== null && ($exceptEntity === null || $exceptEntity->getId() !== $entity->getId())) {
            $this->context->buildViolation($constraint->message)->atPath($constraint->fields[0])->addViolation();
        }
    }
}
