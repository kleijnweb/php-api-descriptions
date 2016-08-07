<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Tests\Description;

use KleijnWeb\ApiDescriptions\Description\Description;
use KleijnWeb\ApiDescriptions\Description\DescriptionFactory;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class DescriptionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function canMakeDescriptionUsingRaml()
    {
        $factory = new DescriptionFactory(DescriptionFactory::BUILDER_RAML);
        $this->assertInstanceOf(Description::class, $factory->create('/foo', (object)[]));
    }

    /**
     * @test
     */
    public function canMakeDescriptionUsingOpenApi()
    {
        $factory = new DescriptionFactory(DescriptionFactory::BUILDER_RAML);
        $this->assertInstanceOf(Description::class, $factory->create('/foo', (object)[]));
    }

    /**
     * @test
     */
    public function willFailOtherwise()
    {
        $factory = new DescriptionFactory('x');
        $this->setExpectedException(\InvalidArgumentException::class);
        $factory->create('/foo', (object)[]);
    }
}
