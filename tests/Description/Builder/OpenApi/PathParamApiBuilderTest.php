<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Builder\OpenApi;

use KleijnWeb\PhpApi\Descriptions\Description\Parameter;
use KleijnWeb\PhpApi\Descriptions\Tests\Description\Builder\OpenApiBuilderTest;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class PathParamApiBuilderTest extends OpenApiBuilderTest
{
    protected function setUp()
    {
        $this->setUpDescription('tests/definitions/openapi/data.yml');
    }

    /**
     * @test
     */
    public function canGetPathParameters()
    {
        $this->assertCount(
            1,
            $this->description
                ->getPath('/entity/{type}')
                ->getPathParameters()
        );
    }

    /**
     * @test
     */
    public function canGetPathParametersFromOperationObject()
    {
        $this->assertInstanceOf(
            Parameter::class,
            $this->description
                ->getPath('/entity/{type}')
                ->getOperation('get')
                ->getParameter('type')
        );
    }
}
