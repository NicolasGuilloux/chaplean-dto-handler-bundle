<?php declare(strict_types=1);

namespace Tests\Chaplean\Bundle\DtoHandlerBundle\Resources\DTO;

use Chaplean\Bundle\DtoHandlerBundle\DataTransferObject\DataTransferObjectInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SubDataTransferObject.
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\Tests\Resources\DTO
 * @author    Nicolas - Chaplean <nicolas@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 */
final class SubDataTransferObject implements DataTransferObjectInterface
{
    /**
     * @var string
     *
     * @Assert\NotNull
     * @Assert\Type("string")
     */
    public $keyname;

    /**
     * @var null
     */
    protected $unaccessible;

    /**
     * @var string|null
     */
    protected $accessible;

    /**
     * @param string|null $accessible
     *
     * @return self
     */
    public function setAccessible(?string $accessible): self
    {
        $this->accessible = $accessible;

        return $this;
    }
}
