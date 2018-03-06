<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Processors\Object;

use KleijnWeb\PhpApi\Descriptions\Description\ComplexType;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object\ComplexTypePropertyProcessor;
use KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\TestHelperFactory;
use KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Types\Category;
use KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Types\Pet;
use KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Types\Tag;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ComplexTypePropertyProcessorTest extends ObjectProcessorTest
{
    /**
     * @test
     */
    public function hydrateWillOmitPropertiesNotInSchema()
    {
        $processor = $this->createProcessor(function (ObjectSchema $schema) {
            return $this->factory($schema, Tag::class);
        }, TestHelperFactory::createPartialPetSchema());

        $this->mockPropertyProcesser
            ->expects($this->once())
            ->method('hydrate')
            ->willReturn(999);

        /** @var Tag $actual */
        $actual = $processor->hydrate((object)['id' => 2, 'name' => 'Fido']);

        $this->assertInstanceOf(Tag::class, $actual);
        $this->assertSame(999, $actual->getId());
    }

    /**
     * @test
     */
    public function hydrateWillOmitPropertiesNotInClass()
    {
        $tagSchema = new ObjectSchema(
            (object)[],
            (object)[
                'id'          => new ScalarSchema((object)['type' => 'integer']),
                'nonExistent' => new ScalarSchema((object)['type' => 'string']),
            ]
        );

        $tagSchema->setComplexType(new ComplexType('Tag', $tagSchema));

        $processor = $this->createProcessor(function (ObjectSchema $schema) {
            return $this->factory($schema, Tag::class);
        }, $tagSchema);

        $this->mockPropertyProcesser
            ->expects($this->once())
            ->method('hydrate')
            ->willReturn(999);

        /** @var Tag $actual */
        $actual = $processor->hydrate((object)['id' => 2, 'nonExistent' => 'value']);

        $this->assertObjectNotHasAttribute('nonExistent', $actual);
    }

    /**
     * @test
     */
    public function hydrateWillDelegateToPropertyProcessor()
    {
        $processor = $this->createProcessor(function (ObjectSchema $schema) {
            return $this->factory($schema, Tag::class);
        }, TestHelperFactory::createTagSchema());

        $this->mockPropertyProcesser
            ->expects($this->once())
            ->method('hydrate')
            ->with(2)
            ->willReturn(999);

        /** @var Tag $actual */
        $actual = $processor->hydrate((object)['id' => 2]);

        $this->assertInstanceOf(Tag::class, $actual);
        $this->assertSame(999, $actual->getId());
    }

    /**
     * @test
     */
    public function dehydrateWillOmitPropertyInClassWhenValueIsNullAndTypeIsNotNull()
    {
        $processor = $this->createProcessor(function (ObjectSchema $schema) {
            return $this->factory($schema, Tag::class);
        }, TestHelperFactory::createTagSchema());

        $this->mockPropertyProcesser
            ->expects($this->once())
            ->method('dehydrate')
            ->with(2)
            ->willReturn(999);

        $tag       = new Tag(2, 'meh');
        $reflector = new \ReflectionObject($tag);
        $property  = $reflector->getProperty('name');
        $property->setAccessible(true);
        $property->setValue($tag, null);

        $actual = $processor->dehydrate($tag);

        $this->assertInstanceOf(\stdClass::class, $actual);
        $this->assertObjectNotHasAttribute('name', $actual);
    }

    /**
     * @test
     */
    public function dehydrateWillDelegateToPropertyProcessorWhenValueNotNull()
    {
        $processor = $this->createProcessor(function (ObjectSchema $schema) {
            return $this->factory($schema, Tag::class);
        }, TestHelperFactory::createTagSchema());

        $this->mockPropertyProcesser
            ->expects($this->any())
            ->method('dehydrate')
            ->willReturnCallback(function ($value) {
                return $value;
            });

        $actual = $processor->dehydrate(new Tag(2, 'meh'));
        $this->assertInstanceOf(\stdClass::class, $actual);
        $this->assertsame(2, $actual->id);
        $this->assertsame('meh', $actual->name);
    }

    /**
     * @test
     */
    public function dehydrateWillDelegateToPropertyProcessorWhenTypeAndValueNull()
    {
        $tagSchema = new ObjectSchema(
            (object)[],
            (object)[
                'id'   => new ScalarSchema((object)['type' => 'integer']),
                'name' => new ScalarSchema((object)['type' => Schema::TYPE_NULL]),
            ]
        );

        $tagSchema->setComplexType(new ComplexType('Tag', $tagSchema));

        $processor = $this->createProcessor(function (ObjectSchema $schema) {
            return $this->factory($schema, Tag::class);
        }, $tagSchema);

        $this->mockPropertyProcesser
            ->expects($this->any())
            ->method('dehydrate')
            ->willReturnCallback(function ($value) {
                return $value;
            });

        $tag       = new Tag(2, 'meh');
        $reflector = new \ReflectionObject($tag);
        $property  = $reflector->getProperty('name');
        $property->setAccessible(true);
        $property->setValue($tag, null);

        $actual = $processor->dehydrate($tag);

        $this->assertInstanceOf(\stdClass::class, $actual);
        $this->assertObjectHasAttribute('name', $actual);
    }

    /**
     * @test
     */
    public function dehydrateWillSetDefaultForPropertiesNotClass()
    {
        $tagSchema = new ObjectSchema(
            (object)[],
            (object)[
                'id'    => new ScalarSchema((object)['type' => 'integer']),
                'name'  => new ScalarSchema((object)['type' => Schema::TYPE_STRING]),
                'extra' => new ScalarSchema(
                    (object)[
                        'type'    => Schema::TYPE_STRING,
                        'default' => 'defaultValue'

                    ]
                ),
            ]
        );

        $tagSchema->setComplexType(new ComplexType('Tag', $tagSchema));

        $processor = $this->createProcessor(function (ObjectSchema $schema) {
            return $this->factory($schema, Tag::class);
        }, $tagSchema);

        $this->mockPropertyProcesser
            ->expects($this->any())
            ->method('dehydrate')
            ->willReturnCallback(function ($value) {
                return $value;
            });

        $actual = $processor->dehydrate(new Tag(2, 'meh'));
        $this->assertInstanceOf(\stdClass::class, $actual);
        $this->assertsame(2, $actual->id);
        $this->assertsame('meh', $actual->name);
        $this->assertsame('defaultValue', $actual->extra);
    }

    /**
     * @test
     */
    public function willHydrateDefault()
    {
        $processor = $this->createProcessor(
            function (ObjectSchema $schema) {
                return $this->factory($schema, Tag::class);
            },
            (object)[
                'id'   => new ScalarSchema((object)[
                    'type' => ScalarSchema::TYPE_INT,
                ]),
                'name' => new ScalarSchema((object)[
                    'type'    => ScalarSchema::TYPE_NULL,
                    'default' => 'theDefaultValue',
                ]),
            ]
        );

        $this->mockPropertyProcesser
            ->expects($this->any())
            ->method('hydrate')
            ->willReturnCallback(function ($value) {
                return $value;
            });

        $object = (object)['id' => 1];

        $data = $processor->hydrate($object);

        $this->assertSame(1, $data->getId());
        $this->assertSame('theDefaultValue', $data->getName());
    }

    /**
     *
     * @test
     */
    public function willOmitNullValuesOnTypedObjectsWhenDehydrating()
    {
        $processor = $this->createProcessor(function (ObjectSchema $schema) {
            return $this->factory($schema, Pet::class);
        }, 'id', 'name', 'status');

        $this->mockPropertyProcesser
            ->expects($this->any())
            ->method('dehydrate')
            ->willReturnCallback(function ($value) {
                return $value;
            });

        $pet = new Pet(1, 'Fido', 'single', 123.12, ['/a', '/b'], new Category(2, 'dogs'), [], (object)[]);

        $refl     = new \ReflectionObject($pet);
        $property = $refl->getProperty('name');
        $property->setAccessible(true);
        $property->setValue($pet, null);

        $data = $processor->dehydrate($pet);

        $this->assertSame(1, $data->id);
        $this->assertObjectNotHasAttribute('name', $data);
    }

    /**
     * @test
     */
    public function willNotOmitNullTypeValuesOnTypedObjectsWhenDehydrating()
    {
        $stub = new class
        {
            private $aInt         = 1;

            private $nullProperty = null;
        };

        $processor = $this->createProcessor(
            function (ObjectSchema $schema) use ($stub) {
                return $this->factory($schema, get_class($stub));
            },
            (object)[
                'aInt'         => new ScalarSchema((object)[
                    'type' => ScalarSchema::TYPE_INT,
                ]),
                'nullProperty' => new ScalarSchema((object)[
                    'type' => ScalarSchema::TYPE_NULL,
                ]),
            ]
        );

        $this->mockPropertyProcesser
            ->expects($this->any())
            ->method('dehydrate')
            ->willReturnCallback(function ($value) {
                return $value;
            });

        $data = $processor->dehydrate($stub);

        $this->assertSame(1, $data->aInt);
        $this->assertObjectHasAttribute('nullProperty', $data);
        $this->assertNull($data->nullProperty);
    }

    protected function factory(ObjectSchema $schema, string $className): ComplexTypePropertyProcessor
    {
        return new ComplexTypePropertyProcessor($schema, $className);
    }
}
