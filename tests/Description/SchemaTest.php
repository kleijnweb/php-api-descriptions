<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Tests\Description;

use KleijnWeb\ApiDescriptions\Description\Schema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SchemaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     *
     * @param \stdClass $definition
     *
     * @dataProvider simpleDefinitionProvider
     */
    public function getWillReturnSameObjectForEqualDefinition(\stdClass $definition = null)
    {
        $schema1 = Schema::get($definition);
        $schema2 = Schema::get($definition);

        $this->assertSame($schema1, $schema2);
    }

    /**
     * @test
     *
     * @param array     $results
     * @param \stdClass $definition
     *
     * @dataProvider getterProvider
     */
    public function getterTest(array $results, \stdClass $definition = null)
    {
        $schema = Schema::get($definition);

        foreach ($results as $methodName => $expected) {
            if ($expected instanceof \stdClass) {
                $this->assertEquals($expected, $schema->$methodName());
                continue;
            }
            $this->assertSame($expected, $schema->$methodName());
        }
    }

    /**
     * @test
     */
    public function canGetNestedArraySchema()
    {
        $schema = Schema::get((object)['type' => 'array', 'items' => (object)['type' => 'number']]);

        $this->assertInstanceOf(Schema::class, $schema->getItemsSchema());
        $this->assertTrue($schema->getItemsSchema()->isType(Schema::TYPE_NUMBER));
    }

    /**
     * @test
     */
    public function canGetNestedPropertySchemas()
    {
        $schema = Schema::get((object)['type' => 'object', 'properties' => ['foo' => (object)['type' => 'string']]]);

        $this->assertInstanceOf(\stdClass::class, $schema->getPropertySchemas());
        $this->assertObjectHasAttribute('foo', $schema->getPropertySchemas());
    }

    /**
     * @test
     */
    public function canGetNestedPropertySchemaByPropertyName()
    {
        $schema = Schema::get(
            (object)[
                'type'       => 'object',
                'properties' => [
                    'foo' => (object)['type' => 'string'],
                    'bar' => (object)['type' => 'number']
                ]
            ]
        );

        $this->assertInstanceOf(Schema::class, $schema->getPropertySchema('bar'));
        $this->assertTrue($schema->getPropertySchema('bar')->isType(Schema::TYPE_NUMBER));
    }

    /**
     * @test
     */
    public function canTestIfHasPropertySchema()
    {
        $schema = Schema::get(
            (object)[
                'type'       => 'object',
                'properties' => [
                    'bar' => (object)['type' => 'number']
                ]
            ]
        );

        $this->assertFalse($schema->hasPropertySchema('x'));
        $this->assertTrue($schema->hasPropertySchema('bar'));
    }

    /**
     * @test
     */
    public function canFailToGetNestedPropertySchemaByPropertyName()
    {
        $schema = Schema::get(
            (object)[
                'type'       => 'object',
                'properties' => [
                    'bar' => (object)['type' => 'number']
                ]
            ]
        );

        $this->setExpectedException(\OutOfBoundsException::class);
        $this->assertInstanceOf(Schema::class, $schema->getPropertySchema('x'));
    }

    public function simpleDefinitionProvider()
    {
        return [
            [null],
            [(object)[]],
            [(object)['type' => 'string']],
            [(object)['type' => 'object', 'properties' => ['foo' => (object)['type' => 'string']]]],
            [(object)['type' => 'object', 'x-type' => 'Foo']],
            [(object)['type' => 'object', 'x-ref-id' => '#/definitions/Foo']],
            [(object)['type' => 'string', 'format' => 'date']],
            [(object)['type' => 'array', 'items' => (object)['type' => 'string']]]
        ];
    }

    /**
     */
    public function getterProvider()
    {
        $calls   = [];
        $results = [
            [
                'isDateTime' => false,
                'getType'    => 'any',
                'getXType'   => null,
                'getXRefId'  => null,
                'getFormat'  => null,
            ],
            [
                'isDateTime' => false,
                'getType'    => 'string',
                'getXType'   => null,
                'getXRefId'  => null,
                'getFormat'  => null,
            ],
            [
                'isDateTime' => false,
                'getType'    => 'string',
                'getXType'   => null,
                'getXRefId'  => null,
                'getFormat'  => null,
            ],
            [
                'isDateTime'         => false,
                'getType'            => 'object',
                'getXType'           => null,
                'getXRefId'          => null,
                'getFormat'          => null,
                'getPropertySchemas' => (object)[
                    'foo' => Schema::get((object)['type' => 'string'])
                ]
            ],
            [
                'isDateTime' => false,
                'getType'    => 'object',
                'getXType'   => 'Foo',
                'getXRefId'  => null,
                'getFormat'  => null,
            ],
            [
                'isDateTime' => false,
                'getType'    => 'object',
                'getXType'   => null,
                'getXRefId'  => '#/definitions/Foo',
                'getFormat'  => null,
            ],
            [
                'isDateTime' => true,
                'getType'    => 'string',
                'getXType'   => null,
                'getXRefId'  => null,
                'getFormat'  => 'date',
            ],
            [
                'isDateTime'     => false,
                'getType'        => 'array',
                'getXType'       => null,
                'getXRefId'      => null,
                'getFormat'      => null,
                'getItemsSchema' => Schema::get((object)['type' => 'string'])
            ],
        ];

        foreach ($this->simpleDefinitionProvider() as $i => $args) {
            $definition = $args[0];
            $calls[]    = [$results[$i], $definition];
        }

        return $calls;
    }
}
