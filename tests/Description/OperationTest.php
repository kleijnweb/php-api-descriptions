<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Description;

use KleijnWeb\PhpApi\Descriptions\Description\Operation;
use KleijnWeb\PhpApi\Descriptions\Description\Parameter;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class OperationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function canGetParameter()
    {
        $schema = new ScalarSchema((object)['type' => Schema::TYPE_ANY]);

        $parameters = [
            new Parameter('bar', false, $schema, Parameter::IN_QUERY)
        ];

        $operation = new Operation('', '/foo', 'get', $parameters, $schema, []);

        $this->assertInstanceOf(Parameter::class, $operation->getParameter('bar'));
    }

    /**
     * @test
     */
    public function willThrowExceptionIfParameterDoesNotExist()
    {
        $operation = new Operation('', '/foo', 'get', [], new ScalarSchema((object)['type' => Schema::TYPE_ANY]), []);

        $this->expectException(\OutOfBoundsException::class);

        $this->assertInstanceOf(Parameter::class, $operation->getParameter('bar'));
    }

    /**
     * @test
     */
    public function canGetRequestBodyParameter()
    {
        $schema = new ScalarSchema((object)['type' => Schema::TYPE_ANY]);

        $parameters = [
            new Parameter('foo', false, $schema, Parameter::IN_QUERY),
            new Parameter('bar', false, $schema, Parameter::IN_BODY),
            new Parameter('bah', false, $schema, Parameter::IN_QUERY)
        ];

        $operation = new Operation('', '/foo', 'post', $parameters, $schema, []);

        $this->assertInstanceOf(Parameter::class, $bodyParameter = $operation->getRequestBodyParameter());

        $this->assertSame('bar', $bodyParameter->getName());
    }

    /**
     * @test
     */
    public function canGetMethod()
    {
        $operation = new Operation('', '/foo', 'put', [], new ScalarSchema((object)['type' => Schema::TYPE_ANY]), []);

        $this->assertSame('put', $operation->getMethod());
    }

    /**
     * @test
     */
    public function gettingRequestBodyParameterWillReturnNullIfOperationHasNoBodyParameter()
    {
        $schema = new ScalarSchema((object)['type' => Schema::TYPE_ANY]);

        $parameters = [
            new Parameter('foo', false, $schema, Parameter::IN_QUERY),
            new Parameter('bah', false, $schema, Parameter::IN_QUERY)
        ];

        $operation = new Operation('', '/foo', 'post', $parameters, $schema, []);

        $this->assertNull($operation->getRequestBodyParameter());
    }
}
