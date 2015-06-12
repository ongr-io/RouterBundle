<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle;

use ONGR\RouterBundle\DependencyInjection\Compiler\SeoAnalyzerAwarePass;
use ONGR\RouterBundle\DependencyInjection\Compiler\SetRouterPass;
use Symfony\Cmf\Component\Routing\DependencyInjection\Compiler\RegisterRoutersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle.
 */
class ONGRRouterBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SetRouterPass());
        $container->addCompilerPass(new SeoAnalyzerAwarePass());
        $container->addCompilerPass(new RegisterRoutersPass('ongr_router.chain_router'));
    }
}
