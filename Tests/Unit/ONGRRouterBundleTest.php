<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Tests\Unit;

use ONGR\RouterBundle\ONGRRouterBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ONGRRouterBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests if build method adds all compiler passes.
     */
    public function testBuildMethod()
    {
        $container = new ContainerBuilder();

        $bundle = new ONGRRouterBundle();
        $bundle->build($container);

        $callback = function (&$value) {
            $value = get_class($value);
        };
        $passes = $container->getCompilerPassConfig()->getPasses();
        array_walk($passes, $callback);

        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/../../DependencyInjection/Compiler/');

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $namespace = 'ONGR\\RouterBundle\\DependencyInjection\\Compiler\\' . $filename;
            $this->assertTrue(in_array($namespace, $passes));
        }
    }
}
