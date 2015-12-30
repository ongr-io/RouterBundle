<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ChainRouterImplementationTest extends WebTestCase
{
    /**
     * Check router instance.
     */
    public function testRouterInstance()
    {
        $router = self::createClient()->getContainer()->get('router');

        $this->assertInstanceOf('Symfony\Cmf\Component\Routing\ChainRouter', $router);
    }

    /**
     * Check router instance.
     */
    public function testSymfonyRouter()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('OK', $crawler->html());
    }
}
