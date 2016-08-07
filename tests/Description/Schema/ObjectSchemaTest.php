<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Tests\Description\Schema;

use KleijnWeb\ApiDescriptions\Description\ComplexType;
use KleijnWeb\ApiDescriptions\Description\Schema\ObjectSchema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ObjectSchemaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function cannotChangeComplexType()
    {
        $schema = new ObjectSchema((object)[]);
        $schema->setComplexType(new ComplexType('Foo', $schema));
        $this->setExpectedException(\LogicException::class);
        $schema->setComplexType(new ComplexType('Foo', $schema));
    }
}
