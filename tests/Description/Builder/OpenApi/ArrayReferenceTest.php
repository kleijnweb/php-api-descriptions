<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Builder\OpenApi;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ArraySchema;
use KleijnWeb\PhpApi\Descriptions\Tests\Description\Builder\OpenApiBuilderTest;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ArrayReferenceTest extends OpenApiBuilderTest
{
    protected function setUp()
    {
        $this->setUpDescription('tests/definitions/openapi/data.yml');
    }

    public function testArrayReferenceSchemaIsArraySchema()
    {
        self::assertInstanceOf(
            ArraySchema::class,
            $this->description
                ->getPath('/entity/{type}/findByCriteria')
                ->getOperation('post')
                ->getParameter('criteria')
                ->getSchema()
        );
    }
}
