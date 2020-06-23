<?php declare(strict_types=1);

/*
 * This file is part of the DtoHandlerBundle package.
 *
 * (c) Chaplean.coop <contact@chaplean.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class DummyEntity
 *
 * @package   Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\Entity
 * @author    Nicolas - Chaplean <nicolas@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 *
 * @ORM\Entity
 */
class DummyEntity
{
    public $name;

    public $type;

    public $other;

    /**
     * @return integer
     */
    public function getId(): int
    {
        return 1;
    }
}
