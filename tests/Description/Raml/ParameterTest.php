<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Tests\Description\Standard\Raml;

use KleijnWeb\ApiDescriptions\Description\Parameter;
use KleijnWeb\ApiDescriptions\Description\Schema;
use KleijnWeb\ApiDescriptions\Description\Standard\Raml\RamlParameter;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ParameterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function creatingParameterWillWithInBodyWillForceTypeObject()
    {
        $parameter = new RamlParameter(
            'foo',
            Parameter::IN_BODY,
            (object)['schema' => (object)['type' => 'string']]
        );
        $this->assertTrue($parameter->getSchema()->isType(Schema::TYPE_OBJECT));
    }

    /**
     * @test
     */
    public function testGetters()
    {
        $parameter = new RamlParameter(
            'bar',
            Parameter::IN_PATH,
            (object)[
                'type'     => 'string',
                'schema'   => (object)['type' => 'string'],
                'enum'     => [1, 2, 3, 4],
                'pattern'  => '/\d+/',
                'required' => true,
            ]
        );
        $map       = [
            'getName'    => 'bar',
            'getIn'      => Parameter::IN_PATH,
            'getSchema'  => Schema::get((object)['type' => 'string']),
            'getEnum'    => [1, 2, 3, 4],
            'getPattern' => '/\d+/',
            'isRequired' => true

        ];
        foreach ($map as $methodName => $value) {
            $this->assertSame($value, $parameter->$methodName());
        }
    }
}
