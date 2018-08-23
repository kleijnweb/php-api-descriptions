<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Builder\OpenApi;

use KleijnWeb\PhpApi\Descriptions\Description\ComplexType;
use KleijnWeb\PhpApi\Descriptions\Tests\Description\Builder\OpenApiBuilderTest;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class DefinitionsBuilderTest extends OpenApiBuilderTest
{
    protected function setUp()
    {
        $this->setUpDescription(
            __DIR__ . '/../../../definitions/openapi/definitions.yml',
            [
                'KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Types\Random',
            ]
        );
    }

    /**
     * @test
     */
    public function willAddAllSchemaUnderDefinitionsAsComplexTypes()
    {
        $typeNames = array_map(function (ComplexType $type) {
            return $type->getName();
        }, $this->description->getComplexTypes());

        sort($typeNames);

        $expected = [
            'Bar',
            'Baz',
            'Foo',
            'PolymorhpicPropertiesObject',
            'UnusedDefinition',
        ];

        $this->assertSame($expected, $typeNames);
    }
}
