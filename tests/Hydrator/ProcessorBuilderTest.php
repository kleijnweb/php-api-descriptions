<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator;

use KleijnWeb\PhpApi\Descriptions\Description\ComplexType;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\AnySchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ArraySchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Exception\UnsupportedException;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\AnyProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\ArrayProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Factory\ComplexTypeFactory;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Factory\Factory;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Factory\ScalarFactory;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object\ComplexTypePropertyProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object\LooseSimpleObjectProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object\ObjectProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object\StrictSimpleObjectProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Processor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar\BoolProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar\DateTimeProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar\IntegerProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar\NullProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar\NumberProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar\StringProcessor;
use KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\TestHelperFactory;
use KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Types\Pet;
use KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Types\Tag;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ProcessorBuilderTest extends TestCase
{
    /**
     * @param Schema $schema
     * @param string $expectedType
     * @test
     * @dataProvider dataProvider
     */
    public function willCreateCorrectProcessorForSchemas(Schema $schema, string $expectedType)
    {
        $builder = new ProcessorBuilder(
            TestHelperFactory::createClassNameResolver(),
            new DateTimeSerializer()
        );
        $this->assertInstanceOf(
            $expectedType,
            $builder->build($schema)
        );
    }

    /**
     * @test
     */
    public function canInjectCustomScalarProcessor()
    {
        $builder = new ProcessorBuilder(
            TestHelperFactory::createClassNameResolver(),
            new DateTimeSerializer()
        );
        $builder->add(
            new class implements Factory
            {
                public function create(Schema $schema, ProcessorBuilder $builder)
                {
                    if (!$schema instanceof ScalarSchema) {
                        return null;
                    }

                    return new class($schema) extends Processor
                    {
                        public function hydrate($value)
                        {
                            return 42;
                        }

                        public function dehydrate($value)
                        {
                            return 'still 42';
                        }
                    };
                }

                public function getPriority(): int
                {
                    return ScalarFactory::PRIORITY + 1;
                }
            }
        );

        $processor = $builder->build(TestHelperFactory::createFullPetSchema());

        /** @var Pet $actual */
        $actual = $processor->hydrate((object)['id' => 999]);
        $this->assertSame(42, $actual->getId());

        /** @var \stdClass $actual */
        $actual = $processor->dehydrate($actual);
        $this->assertSame('still 42', $actual->id);
    }

    /**
     * @test
     */
    public function canInjectCustomObjectProcessor()
    {
        $builder = new ProcessorBuilder(
            $classNameResolver = TestHelperFactory::createClassNameResolver(),
            new DateTimeSerializer()
        );

        $builder->add(
            new class($classNameResolver) extends ComplexTypeFactory
            {
                public function supports(Schema $schema)
                {
                    if (!parent::supports($schema)) {
                        return false;
                    }

                    /** @var ObjectSchema $schema */
                    return 'Tag' === $schema->getComplexType()->getName();
                }

                public function getPriority(): int
                {
                    return ComplexTypeFactory::PRIORITY + 1;
                }

                protected function instantiate(ObjectSchema $schema, ProcessorBuilder $builder): ObjectProcessor
                {
                    $className = $this->classNameResolver->resolve($schema->getComplexType()->getName());

                    return new class($schema, $className) extends ComplexTypePropertyProcessor
                    {
                        private $identityMap = [];

                        protected function getObjectForHydration(\stdClass $object)
                        {
                            if (isset($this->identityMap[$object->id])) {
                                return $this->identityMap[$object->id];
                            }

                            return $this->identityMap[$object->id] = parent::getObjectForHydration($object);
                        }
                    };
                }
            }
        );

        $processor = $builder->build(TestHelperFactory::createTagSchema());

        /** @var Tag $actual */
        $tag1 = $processor->hydrate((object)['id' => 1]);
        $tag2 = $processor->hydrate((object)['id' => 1]);
        $tag3 = $processor->hydrate((object)['id' => 2]);
        $this->assertSame($tag1, $tag2);
        $this->assertNotSame($tag2, $tag3);

        $processor = $builder->build(TestHelperFactory::createFullPetSchema());

        /** @var Pet $actual */
        $pet1 = $processor->hydrate((object)['id' => 1]);
        $pet2 = $processor->hydrate((object)['id' => 1]);
        $pet3 = $processor->hydrate((object)['id' => 2]);
        $this->assertNotSame($pet1, $pet2);
        $this->assertNotSame($pet2, $pet3);
    }

    /**
     * @test
     */
    public function willFailOnUnknownSchema()
    {
        $builder = new ProcessorBuilder(
            TestHelperFactory::createClassNameResolver(),
            new DateTimeSerializer()
        );
        $this->expectException(UnsupportedException::class);

        /** @var Schema $schema */
        $schema = $this->getMockBuilder(Schema::class)->disableOriginalConstructor()->getMockForAbstractClass();
        $builder->build($schema);
    }

    public static function dataProvider(): array
    {
        return [
            [new ScalarSchema((object)['type' => Schema::TYPE_BOOL]), BoolProcessor::class],
            [new ScalarSchema((object)['type' => Schema::TYPE_INT]), IntegerProcessor::class],
            [new ScalarSchema((object)['type' => Schema::TYPE_NUMBER]), NumberProcessor::class],
            [new ScalarSchema((object)['type' => Schema::TYPE_NULL]), NullProcessor::class],
            [new ScalarSchema((object)['type' => Schema::TYPE_STRING]), StringProcessor::class],
            [
                new ScalarSchema((object)['type' => Schema::TYPE_STRING, 'format' => Schema::FORMAT_DATE]),
                DateTimeProcessor::class,
            ],
            [
                new ScalarSchema((object)['type' => Schema::TYPE_STRING, 'format' => Schema::FORMAT_DATE_TIME]),
                DateTimeProcessor::class,
            ],
            [new AnySchema(), AnyProcessor::class],
            [new ArraySchema((object)[], new AnySchema()), ArrayProcessor::class],
            [new ObjectSchema((object)[], (object)['id' => new AnySchema(),]), LooseSimpleObjectProcessor::class],
            [
                new ObjectSchema(
                    (object)['additionalProperties' => true,],
                    (object)['id' => new AnySchema(),]
                ),
                LooseSimpleObjectProcessor::class,
            ],
            [
                new ObjectSchema((object)['additionalProperties' => false], (object)['id' => new AnySchema(),]),
                StrictSimpleObjectProcessor::class,
            ],
            [self::createComplexSchema(), ComplexTypePropertyProcessor::class,],
        ];
    }


    public static function createComplexSchema(): ObjectSchema
    {
        $schema = new ObjectSchema((object)[], (object)['id' => new AnySchema()]);
        $schema->setComplexType(new ComplexType('Pet', $schema));

        return $schema;
    }
}
