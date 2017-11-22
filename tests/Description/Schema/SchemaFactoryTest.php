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
     * @test
     *
     * @param \stdClass $definition
     *
     * @dataProvider simpleDefinitionProvider
     */
    public function getWillReturnSameObjectForEqualDefinition(\stdClass $definition = null)
    {
        $schema1 = self::$factory->create($definition);
        $schema2 = self::$factory->create($definition);

        $this->assertSame($schema1, $schema2);
    }

    /**
     * @test
     */
    public function canGetNestedArraySchema()
    {
        /** @var ArraySchema $schema */
        $schema = self::$factory->create((object)['type' => 'array', 'items' => (object)['type' => 'number']]);

        $this->assertInstanceOf(Schema::class, $schema->getItemsSchema());
        $this->assertTrue($schema->getItemsSchema()->isType(Schema::TYPE_NUMBER));
    }

    /**
     * @test
     */
    public function canGetNestedPropertySchemas()
    {
        /** @var ObjectSchema $schema */
        $schema = self::$factory->create((object)[
            'type'       => 'object',
            'properties' => ['foo' => (object)['type' => 'string']]
        ]);

        $this->assertInstanceOf(\stdClass::class, $schema->getPropertySchemas());
        $this->assertObjectHasAttribute('foo', $schema->getPropertySchemas());
    }

    /**
     * @test
     */
    public function canGetNestedPropertySchemaByPropertyName()
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

        $this->assertInstanceOf(Schema::class, $schema->getPropertySchema('bar'));
        $this->assertTrue($schema->getPropertySchema('bar')->isType(Schema::TYPE_NUMBER));
    }

    /**
     * @test
     */
    public function canTestIfHasPropertySchema()
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

        $this->assertFalse($schema->hasPropertySchema('x'));
        $this->assertTrue($schema->hasPropertySchema('bar'));
    }

    /**
     * @test
     */
    public function canFailToGetNestedPropertySchemaByPropertyName()
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

        $this->expectException(\OutOfBoundsException::class);
        $this->assertInstanceOf(Schema::class, $schema->getPropertySchema('x'));
    }

    /**
     * @test
     */
    public function schemaTypeWillDefaultToLastNestedType()
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

        $this->assertSame('integer', $schema->getType());
    }

    /**
     * @test
     */
    public function willMergePropertiesWhenUsingAllOf()
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

        $this->assertInstanceOf(ScalarSchema::class, $schema->getPropertySchema('foo'));
        $this->assertInstanceOf(ScalarSchema::class, $schema->getPropertySchema('bar'));
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
