<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Tests\Unit\DependencyInjection\Compiler;

use ONGR\RouterBundle\DependencyInjection\Compiler\SetRouterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class SetRouterPassTest.
 */
class SetRouterPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Provider for testProcess.
     *
     * @return array
     */
    public function processProvider()
    {
        $cases = [];

        // Case #0. Default config.
        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->expects($this->exactly(3))->method('hasParameter')->withConsecutive(
            ['ongr_router.use_chain_router'],
            ['ongr_router.add_default_router'],
            ['ongr_router.add_ongr_router']
        )->willReturn(true, true, true);

        $container->expects($this->exactly(3))->method('getParameter')->withConsecutive(
            ['ongr_router.use_chain_router'],
            ['ongr_router.add_default_router'],
            ['ongr_router.add_ongr_router']
        )->willReturnOnConsecutiveCalls(true, true, true);

        $container->expects($this->once())->method('setAlias')->with('router', 'ongr_router.chain_router');
        /** @var Definition|\PHPUnit_Framework_MockObject_MockObject $chainRouter*/
        $chainRouter = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $container->expects($this->once())->method('getDefinition')->with('ongr_router.chain_router')
            ->willReturn($chainRouter);

        $chainRouter->expects($this->exactly(2))->method('addMethodCall')->withConsecutive(
            ['add', [new Reference('router.default'), 100]],
            ['add', [new Reference('ongr_router.router'), -100]]
        );

        $cases[] = [$container];

        // Case #1. Disabled.
        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->expects($this->exactly(3))->method('hasParameter')->withConsecutive(
            ['ongr_router.use_chain_router'],
            ['ongr_router.add_default_router'],
            ['ongr_router.add_ongr_router']
        )->willReturn(true, true, true);

        $container->expects($this->exactly(3))->method('getParameter')->withConsecutive(
            ['ongr_router.use_chain_router'],
            ['ongr_router.add_default_router'],
            ['ongr_router.add_ongr_router']
        )->willReturnOnConsecutiveCalls(false, false, false);

        $container->expects($this->never())->method('setAlias');
        /** @var Definition|\PHPUnit_Framework_MockObject_MockObject $chainRouter*/
        $chainRouter = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $container->expects($this->once())->method('getDefinition')->with('ongr_router.chain_router')
            ->willReturn($chainRouter);

        $chainRouter->expects($this->never())->method('addMethodCall');

        $cases[] = [$container];

        // Case #2. Only chain router.
        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->expects($this->exactly(3))->method('hasParameter')->withConsecutive(
            ['ongr_router.use_chain_router'],
            ['ongr_router.add_default_router'],
            ['ongr_router.add_ongr_router']
        )->willReturn(true, true, true);

        $container->expects($this->exactly(3))->method('getParameter')->withConsecutive(
            ['ongr_router.use_chain_router'],
            ['ongr_router.add_default_router'],
            ['ongr_router.add_ongr_router']
        )->willReturnOnConsecutiveCalls(true, false, false);

        $container->expects($this->once())->method('setAlias')->with('router', 'ongr_router.chain_router');
        /** @var Definition|\PHPUnit_Framework_MockObject_MockObject $chainRouter*/
        $chainRouter = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $container->expects($this->once())->method('getDefinition')->with('ongr_router.chain_router')
            ->willReturn($chainRouter);

        $chainRouter->expects($this->never())->method('addMethodCall');

        $cases[] = [$container];

        // Case #3. Only default router.
        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->expects($this->exactly(3))->method('hasParameter')->withConsecutive(
            ['ongr_router.use_chain_router'],
            ['ongr_router.add_default_router'],
            ['ongr_router.add_ongr_router']
        )->willReturn(true, true, true);

        $container->expects($this->exactly(3))->method('getParameter')->withConsecutive(
            ['ongr_router.use_chain_router'],
            ['ongr_router.add_default_router'],
            ['ongr_router.add_ongr_router']
        )->willReturnOnConsecutiveCalls(false, true, false);

        $container->expects($this->never())->method('setAlias');
        /** @var Definition|\PHPUnit_Framework_MockObject_MockObject $chainRouter*/
        $chainRouter = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $container->expects($this->once())->method('getDefinition')->with('ongr_router.chain_router')
            ->willReturn($chainRouter);

        $chainRouter->expects($this->exactly(1))->method('addMethodCall')->with(
            'add',
            [new Reference('router.default'), 100]
        );

        $cases[] = [$container];

        // Case #4. Only ongr router.
        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->expects($this->exactly(3))->method('hasParameter')->withConsecutive(
            ['ongr_router.use_chain_router'],
            ['ongr_router.add_default_router'],
            ['ongr_router.add_ongr_router']
        )->willReturn(true, true, true);

        $container->expects($this->exactly(3))->method('getParameter')->withConsecutive(
            ['ongr_router.use_chain_router'],
            ['ongr_router.add_default_router'],
            ['ongr_router.add_ongr_router']
        )->willReturnOnConsecutiveCalls(false, false, true);

        $container->expects($this->never())->method('setAlias');
        /** @var Definition|\PHPUnit_Framework_MockObject_MockObject $chainRouter*/
        $chainRouter = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $container->expects($this->once())->method('getDefinition')->with('ongr_router.chain_router')
            ->willReturn($chainRouter);

        $chainRouter->expects($this->exactly(1))->method('addMethodCall')->with(
            'add',
            [new Reference('ongr_router.router'), -100]
        );

        $cases[] = [$container];

        return $cases;
    }

    /**
     * Tests set router pass.
     *
     * @param ContainerBuilder $container
     *
     * @dataProvider processProvider
     */
    public function testProcess(ContainerBuilder $container)
    {
        $pass = new SetRouterPass();
        $pass->process($container);
    }
}
