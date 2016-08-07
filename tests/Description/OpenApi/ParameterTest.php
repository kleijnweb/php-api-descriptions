<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Tests\Description\Standard\OpenApi;

use KleijnWeb\ApiDescriptions\Description\Parameter;
use KleijnWeb\ApiDescriptions\Description\Schema;
use KleijnWeb\ApiDescriptions\Description\Standard\OpenApi\OpenApiParameter;

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
        $parameter = new OpenApiParameter(
            (object)[
                'name'   => 'foo',
                'in'     => Parameter::IN_BODY,
                'schema' => (object)['type' => 'string']
            ]
        );
        $this->assertTrue($parameter->getSchema()->isType(Schema::TYPE_OBJECT));
    }

    /**
     * @test
     */
    public function testGetters()
    {
        $parameter = new OpenApiParameter(
            (object)[
                'name'             => 'bar',
                'in'               => Parameter::IN_PATH,
                'type'             => 'string',
                'schema'           => (object)['type' => 'string'],
                'enum'             => [1, 2, 3, 4],
                'pattern'          => '/\d+/',
                'collectionFormat' => 'csv',
                'required'         => true,
            ]
        );
        $map       = [
            'getName'             => 'bar',
            'getIn'               => Parameter::IN_PATH,
            'getSchema'           => Schema::get((object)['type' => 'string']),
            'getEnum'             => [1, 2, 3, 4],
            'getPattern'          => '/\d+/',
            'getCollectionFormat' => 'csv',
            'isRequired'          => true

        ];
        foreach ($map as $methodName => $value) {
            $this->assertSame($value, $parameter->$methodName());
        }
    }
}
