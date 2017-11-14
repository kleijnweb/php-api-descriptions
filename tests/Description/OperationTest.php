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
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class OperationTest extends TestCase
{
    public function testCanGetParameter()
    {
        $schema = new ScalarSchema((object)['type' => Schema::TYPE_ANY]);

        $parameters = [
            new Parameter('bar', false, $schema, Parameter::IN_QUERY)
        ];

        $operation = new Operation('', '/foo', 'get', $parameters, $schema, []);

        self::assertInstanceOf(Parameter::class, $operation->getParameter('bar'));
    }

    public function testWillThrowExceptionIfParameterDoesNotExist()
    {
        $operation = new Operation('', '/foo', 'get', [], new ScalarSchema((object)['type' => Schema::TYPE_ANY]), []);

        self::expectException(\OutOfBoundsException::class);

        self::assertInstanceOf(Parameter::class, $operation->getParameter('bar'));
    }

    public function testCanGetRequestBodyParameter()
    {
        $schema = new ScalarSchema((object)['type' => Schema::TYPE_ANY]);

        $parameters = [
            new Parameter('foo', false, $schema, Parameter::IN_QUERY),
            new Parameter('bar', false, $schema, Parameter::IN_BODY),
            new Parameter('bah', false, $schema, Parameter::IN_QUERY)
        ];

        $operation = new Operation('', '/foo', 'post', $parameters, $schema, []);

        self::assertInstanceOf(Parameter::class, $bodyParameter = $operation->getRequestBodyParameter());

        self::assertSame('bar', $bodyParameter->getName());
    }

    public function testCanGetMethod()
    {
        $operation = new Operation('', '/foo', 'put', [], new ScalarSchema((object)['type' => Schema::TYPE_ANY]), []);

        self::assertSame('put', $operation->getMethod());
    }

    public function testGettingRequestBodyParameterWillReturnNullIfOperationHasNoBodyParameter()
    {
        $schema = new ScalarSchema((object)['type' => Schema::TYPE_ANY]);

        $parameters = [
            new Parameter('foo', false, $schema, Parameter::IN_QUERY),
            new Parameter('bah', false, $schema, Parameter::IN_QUERY)
        ];

        $operation = new Operation('', '/foo', 'post', $parameters, $schema, []);

        self::assertNull($operation->getRequestBodyParameter());
    }
}
