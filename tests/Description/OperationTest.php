<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Tests\Description;

use KleijnWeb\ApiDescriptions\Description\Operation;
use KleijnWeb\ApiDescriptions\Description\Parameter;
use KleijnWeb\ApiDescriptions\Description\Schema\ScalarSchema;
use KleijnWeb\ApiDescriptions\Description\Schema\Schema;

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
        $path       = new Operation('/foo', 'get', $parameters, $schema, []);

        $this->assertInstanceOf(Parameter::class, $path->getParameter('bar'));
    }

    /**
     * @test
     */
    public function willThrowExceptionIfParameterDoesNotExist()
    {
        $path = new Operation('/foo', 'get', [], new ScalarSchema((object)['type' => Schema::TYPE_ANY]), []);

        $this->setExpectedException(\OutOfBoundsException::class);

        $this->assertInstanceOf(Parameter::class, $path->getParameter('bar'));
    }
}
