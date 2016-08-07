<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Tests\Description\Standard\Raml;

use KleijnWeb\ApiDescriptions\Description\Parameter;
use KleijnWeb\ApiDescriptions\Description\Standard\Raml\RamlOperation;

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
        $path = new RamlOperation(
            (object)[
                'queryParameters' => [
                    'bar' => (object)['type' => 'string']
                ]
            ],
            '/foo/bar',
            'get'
        );

        $this->assertInstanceOf(Parameter::class, $path->getParameter('bar'));
    }

    /**
     * @test
     */
    public function willThrowExceptionIfParameterDoesNotExist()
    {
        $path = new RamlOperation(
            (object)[],
            '/foo/bar',
            'get'
        );

        $this->setExpectedException(\OutOfBoundsException::class);

        $this->assertInstanceOf(Parameter::class, $path->getParameter('bar'));
    }
}