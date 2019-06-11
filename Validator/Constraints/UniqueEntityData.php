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

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * Class UniqueEntityData.
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\Validator\Constraints
 * @author    Nicolas - Chaplean <nicolas@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 *
 * @Annotation
 */
class UniqueEntityData extends Constraint
{
    /**
     * @var string
     */
    public $entityClass;

    /**
     * @var mixed
     */
    public $except;

    /**
     * @var array
     */
    public $fields;

    /**
     * @var string
     */
    public $message;

    /**
     * UniqueEntityData constructor.
     *
     * @param $options
     */
    public function __construct($options = null)
    {
        if (!\array_key_exists('entityClass', $options) || !\array_key_exists('fields', $options)) {
            throw new MissingOptionsException('', $options);
        }

        $this->entityClass = $options['entityClass'];
        $this->except = $options['except'] ?? null;
        $this->fields = \is_array($options['fields']) ? $options['fields'] : [$options['fields']];
        $this->message = $options['message'] ?? 'This value is already used.';

        parent::__construct();
    }

    /**
     * @return string
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
