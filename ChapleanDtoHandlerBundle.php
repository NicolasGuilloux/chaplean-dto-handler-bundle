<?php
/*
 *
 * This file is part of the DtoHandlerBundle package.
 *
 * (c) Chaplean.coop <contact@chaplean.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chaplean\Bundle\DtoHandlerBundle;

use Chaplean\Bundle\DtoHandlerBundle\DependencyInjection\Compiler\DataTransferObjectPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ChapleanDtoHandlerBundle.
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle
 * @author    Nicolas - Chaplean <nicolas@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 * @since     1.0.0
 */
class ChapleanDtoHandlerBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new DataTransferObjectPass());
    }
}
