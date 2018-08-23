<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Processors\Object;

use KleijnWeb\PhpApi\Descriptions\Description\ComplexType;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object\ComplexTypePropertyProcessor;
use KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Types\Polymorphism\DiamondAType;
use KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Types\Polymorphism\DiamondBType;
use KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Types\Polymorphism\DiamondCType;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ComplexTypePropertyProcessorPolymorphismTest extends ObjectProcessorTest
{
    /**
     * @var ObjectSchema
     */
    private $typeASchema;

    /**
     * @var ObjectSchema
     */
    private $typeBSchema;

    /**
     * @var ObjectSchema
     */
    private $typeCSchema;

    /**
     * @var ObjectSchema
     */
    private $typeDSchema;

    protected function setUp()
    {
        parent::setUp();

        $this->createSchema();
    }

    /**
     * Dehydrate TypeA using schema for TypeB
     *
     * @test
     */
    public function canDehydrateParent()
    {
        $processor = $this->createProcessor(
            function (ObjectSchema $schema) {
                return $this->factory($schema, DiamondBType::class);
            },
            $this->typeBSchema
        );

        $processor->setPropertyProcessor('typeAProperty', $this->propertyProcessor);

        $expectedValue = 'value';

        $this->mockPropertyProcessor
            ->expects($this->once())
            ->method('dehydrate')
            ->willReturn($expectedValue);

        /** @var \stdClass $actual */
        $actual = $processor->dehydrate(
            (new DiamondAType())
                ->setTypeAProperty('value')
        );

        $this->assertSame($expectedValue, $actual->typeAProperty);
    }

    /**
     * Dehydrate TypeB using schema for TypeA, with properties not present in TypeA present
     *
     * @test
     */
    public function canDehydrateChild()
    {
        $processor = $this->createProcessor(
            function (ObjectSchema $schema) {
                return $this->factory($schema, DiamondAType::class);
            },
            $this->typeASchema
        );

        $processor->setPropertyProcessor('typeAProperty', $this->propertyProcessor);
        $processor->setPropertyProcessor('typeBProperty', $this->propertyProcessor);

        $this->mockPropertyProcessor
            ->expects($this->any())
            ->method('dehydrate')
            ->willReturnCallback(function (string $value) {
                return $value;
            });

        /** @var \stdClass $actual */
        $actual = $processor->dehydrate(
            (new DiamondBType())
                ->setTypeBProperty('typeBValue')
                ->setTypeAProperty('typeAValue')
        );

        $this->assertSame('typeAValue', $actual->typeAProperty);
        $this->assertSame('typeBValue', $actual->typeBProperty);
    }

    /**
     * Hydrate TypeA using schema for TypeB
     *
     * @test
     */
    public function canHydrateParent()
    {
        $processor = $this->createProcessor(
            function (ObjectSchema $schema) {
                return $this->factory($schema, DiamondBType::class);
            },
            $this->typeBSchema
        );

        $processor->setPropertyProcessor('typeAProperty', $this->propertyProcessor);
        $processor->setPropertyProcessor('typeBProperty', $this->propertyProcessor);

        $this->mockPropertyProcessor
            ->expects($this->any())
            ->method('hydrate')
            ->willReturnCallback(function (string $value) {
                return $value;
            });

        /** @var DiamondAType $actual */
        $actual = $processor->hydrate(
            (object)[
                'x-type-name'   => 'DiamondAType',
                'typeAProperty' => 'typeAValue',
                // Extra data is ignored
                'typeBProperty' => 'typeBValue',
            ]
        );

        $this->assertInstanceOf(DiamondAType::class, $actual);
        $this->assertSame('typeAValue', $actual->getTypeAProperty());
    }

    /**
     * Hydrate TypeB using schema for TypeA
     *
     * @test
     */
    public function canHydrateChild()
    {
        $processor = $this->createProcessor(
            function (ObjectSchema $schema) {
                return $this->factory($schema, DiamondAType::class);
            },
            $this->typeASchema
        );

        $processor->setPropertyProcessor('typeAProperty', $this->propertyProcessor);
        $processor->setPropertyProcessor('typeBProperty', $this->propertyProcessor);

        $this->mockPropertyProcessor
            ->expects($this->any())
            ->method('hydrate')
            ->willReturnCallback(function (string $value) {
                return $value;
            });

        /** @var DiamondBType $actual */
        $actual = $processor->hydrate(
            (object)[
                'x-type-name'   => 'DiamondBType',
                'typeAProperty' => 'typeAValue',
                'typeBProperty' => 'typeBValue',
            ]
        );

        $this->assertInstanceOf(DiamondBType::class, $actual);
        $this->assertSame('typeAValue', $actual->getTypeAProperty());
        $this->assertSame('typeBValue', $actual->getTypeBProperty());
    }

    /**
     * @param ObjectSchema $schema
     *
     * @param string       $className
     *
     * @return ComplexTypePropertyProcessor
     * @throws \ReflectionException
     */
    protected function factory(ObjectSchema $schema, string $className): ComplexTypePropertyProcessor
    {
        return new ComplexTypePropertyProcessor($schema, $className);
    }

    protected function createSchema()
    {
        $this->typeASchema = new ObjectSchema(
            (object)[],
            (object)[
                'typeAProperty' => new ScalarSchema((object)['type' => 'string', 'default' => 'A']),
            ]
        );

        $this->typeBSchema = new ObjectSchema(
            (object)[],
            (object)[
                'typeAProperty' => new ScalarSchema((object)['type' => 'string', 'default' => 'A']),
                'typeBProperty' => new ScalarSchema((object)['type' => 'string', 'default' => 'B']),
            ]
        );

        $this->typeCSchema = new ObjectSchema(
            (object)[],
            (object)[
                'typeAProperty' => new ScalarSchema((object)['type' => 'string', 'default' => 'A']),
                'typeCProperty' => new ScalarSchema((object)['type' => 'string', 'default' => 'C']),
            ]
        );

        $this->typeDSchema = new ObjectSchema(
            (object)[],
            (object)[
                'typeAProperty' => new ScalarSchema((object)['type' => 'string', 'default' => 'A']),
                'typeBProperty' => new ScalarSchema((object)['type' => 'string', 'default' => 'B']),
                'typeCProperty' => new ScalarSchema((object)['type' => 'string', 'default' => 'C']),
                'typeDProperty' => new ScalarSchema((object)['type' => 'string', 'default' => 'D']),
            ]
        );

        $typeAType = new ComplexType('DiamondAType', $this->typeASchema, DiamondAType::class);
        $typeBType = new ComplexType('DiamondBType', $this->typeBSchema, DiamondBType::class);
        $typeCType = new ComplexType('DiamondCType', $this->typeCSchema, DiamondCType::class);
        $typeDType = new ComplexType('DiamondDType', $this->typeDSchema, DiamondCType::class);

        $typeAType
            ->addChild($typeBType)
            ->addChild($typeCType);

        $typeBType
            ->addParent($typeAType)
            ->addChild($typeDType);

        $typeCType
            ->addParent($typeAType)
            ->addChild($typeDType);

        $typeDType
            ->addParent($typeCType)
            ->addParent($typeDType);

        $this->typeASchema->setComplexType($typeAType);
        $this->typeBSchema->setComplexType($typeBType);
        $this->typeCSchema->setComplexType($typeCType);
        $this->typeDSchema->setComplexType($typeDType);
    }
}
