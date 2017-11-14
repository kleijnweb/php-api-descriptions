<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Schema;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ArraySchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\SchemaFactory;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SchemaFactoryTest extends TestCase
{
    /**
     * @var SchemaFactory
     */
    private static $factory;

    public static function setUpBeforeClass()
    {
        self::$factory = new SchemaFactory();
    }

    /**
     * @param \stdClass $definition
     *
     * @dataProvider simpleDefinitionProvider
     */
    public function testGetWillReturnSameObjectForEqualDefinition(\stdClass $definition = null)
    {
        $schema1 = self::$factory->create($definition);
        $schema2 = self::$factory->create($definition);

        self::assertSame($schema1, $schema2);
    }

    public function testCanGetNestedArraySchema()
    {
        /** @var ArraySchema $schema */
        $schema = self::$factory->create((object)['type' => 'array', 'items' => (object)['type' => 'number']]);

        self::assertInstanceOf(Schema::class, $schema->getItemsSchema());
        self::assertTrue($schema->getItemsSchema()->isType(Schema::TYPE_NUMBER));
    }

    public function testCanGetNestedPropertySchemas()
    {
        /** @var ObjectSchema $schema */
        $schema = self::$factory->create((object)[
            'type'       => 'object',
            'properties' => ['foo' => (object)['type' => 'string']]
        ]);

        self::assertInstanceOf(\stdClass::class, $schema->getPropertySchemas());
        self::assertObjectHasAttribute('foo', $schema->getPropertySchemas());
    }

    public function testCanGetNestedPropertySchemaByPropertyName()
    {
        /** @var ObjectSchema $schema */
        $schema = self::$factory->create(
            (object)[
                'type'       => 'object',
                'properties' => [
                    'foo' => (object)['type' => 'string'],
                    'bar' => (object)['type' => 'number']
                ]
            ]
        );

        self::assertInstanceOf(Schema::class, $schema->getPropertySchema('bar'));
        self::assertTrue($schema->getPropertySchema('bar')->isType(Schema::TYPE_NUMBER));
    }

    public function testCanTestIfHasPropertySchema()
    {
        /** @var ObjectSchema $schema */
        $schema = self::$factory->create(
            (object)[
                'type'       => 'object',
                'properties' => [
                    'bar' => (object)['type' => 'number']
                ]
            ]
        );

        self::assertFalse($schema->hasPropertySchema('x'));
        self::assertTrue($schema->hasPropertySchema('bar'));
    }

    public function testCanFailToGetNestedPropertySchemaByPropertyName()
    {
        /** @var ObjectSchema $schema */
        $schema = self::$factory->create(
            (object)[
                'type'       => 'object',
                'properties' => [
                    'bar' => (object)['type' => 'number']
                ]
            ]
        );

        self::expectException(\OutOfBoundsException::class);
        self::assertInstanceOf(Schema::class, $schema->getPropertySchema('x'));
    }

    public function testSchemaTypeWillDefaultToLastNestedType()
    {
        /** @var ObjectSchema $schema */
        $schema = self::$factory->create(
            (object)[
                'allOf' => [
                    (object)['type' => 'number'],
                    (object)['type' => 'integer']
                ]
            ]
        );

        self::assertSame('integer', $schema->getType());
    }

    public function testWillMergePropertiesWhenUsingAllOf()
    {
        /** @var ObjectSchema $schema */
        $schema = self::$factory->create(
            (object)[
                'allOf' => [
                    (object)[
                        'type'       => 'object',
                        'properties' => (object)[
                            'foo' => (object)['type' => 'number']
                        ]
                    ],
                    (object)[
                        'type'       => 'object',
                        'properties' => (object)[
                            'bar' => (object)['type' => 'number']
                        ]
                    ],
                ]
            ]
        );

        self::assertInstanceOf(ScalarSchema::class, $schema->getPropertySchema('foo'));
        self::assertInstanceOf(ScalarSchema::class, $schema->getPropertySchema('bar'));
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
}
