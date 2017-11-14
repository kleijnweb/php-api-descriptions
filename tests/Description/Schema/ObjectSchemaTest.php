<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Schema;

use KleijnWeb\PhpApi\Descriptions\Description\ComplexType;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ObjectSchemaTest extends TestCase
{
    public function testCannotChangeComplexType()
    {
        $schema = new ObjectSchema((object)[]);
        $schema->setComplexType(new ComplexType('Foo', $schema));
        self::expectException(\LogicException::class);
        $schema->setComplexType(new ComplexType('Foo', $schema));
    }
}
