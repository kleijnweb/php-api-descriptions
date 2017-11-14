<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Description;

use KleijnWeb\PhpApi\Descriptions\Description\Description;
use KleijnWeb\PhpApi\Descriptions\Description\DescriptionFactory;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class DescriptionFactoryTest extends TestCase
{
    public function testCanMakeDescriptionUsingRaml()
    {
        $factory = new DescriptionFactory(DescriptionFactory::BUILDER_RAML);
        self::assertInstanceOf(Description::class, $factory->create('/foo', (object)[]));
    }

    public function testCanMakeDescriptionUsingOpenApi()
    {
        $factory = new DescriptionFactory(DescriptionFactory::BUILDER_RAML);
        self::assertInstanceOf(Description::class, $factory->create('/foo', (object)[]));
    }

    public function testWillFailOtherwise()
    {
        $factory = new DescriptionFactory('x');
        self::expectException(\InvalidArgumentException::class);
        $factory->create('/foo', (object)[]);
    }
}
