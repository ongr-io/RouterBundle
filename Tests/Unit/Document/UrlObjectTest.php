<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Tests\Unit\Document;

use ONGR\RouterBundle\Document\UrlObject;

/**
 * Class UrlObject.
 */
class UrlObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testSettersGetters.
     *
     * @return array
     */
    public function provider()
    {
        return [
            [
                'property' => 'url',
                'defaultValue' => null,
                'newValue' => 'string',
            ],
            [
                'property' => 'key',
                'defaultValue' => null,
                'newValue' => 'string',
            ],
        ];
    }

    /**
     * Tests setters and getters.
     *
     * @param string $property
     * @param mixed  $defaultValue
     * @param mixed  $newValue
     *
     * @dataProvider provider
     */
    public function testSettersGetters($property, $defaultValue, $newValue)
    {
        $object = new UrlObject();
        $this->assertEquals($defaultValue, $object->{'get' . ucfirst($property)}());
        $object->{'set' . ucfirst($property)}($newValue);
        $this->assertEquals($newValue, $object->{'get' . ucfirst($property)}());
    }
}
