<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Processors\Object;


use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\AnyProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object\LooseSimpleObjectProcessor;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class LooseSimpleObjectProcessorTest extends SimpleObjectProcessorTest
{
    /**
     * @var MockObject
     */
    private $mockAnyProcessor;

    /**
     * @test
     */
    public function hydrateWillDelegateToAnyProcessorIfPropertyNotInSchemaAndPropertyExists()
    {
        $processor = $this->createProcessor([$this, 'factory'], 'aInt');

        $object = (object)['aInt' => 1, 'anyProperty' => 'anyValue'];

        $this->mockPropertyProcesser
            ->expects($this->once())
            ->method('hydrate')
            ->with(1)
            ->willReturnCallback(function ($value) {
                return $value;
            });

        $this->mockAnyProcessor
            ->expects($this->once())
            ->method('hydrate')
            ->with($object->anyProperty)
            ->willReturnCallback(function ($value) {
                return $value;
            });

        $data = $processor->hydrate($object);

        $this->assertSame(1, $data->aInt);
        $this->assertObjectHasAttribute('anyProperty', $data);
        $this->assertSame($object->anyProperty, $data->anyProperty);
    }

    /**
     * @test
     */
    public function hydrateWillSetDefaultIfPropertyInSchemaAndHasDefaultAndPropertyNotExists()
    {
        $processor = $this->createProcessor(
            function (ObjectSchema $schema) {
                return $this->factory($schema);
            },
            (object)[
                'id'   => new ScalarSchema((object)[
                    'type' => ScalarSchema::TYPE_INT,
                ]),
                'name' => new ScalarSchema((object)[
                    'type'    => ScalarSchema::TYPE_NULL,
                    'default' => 'theDefaultValue',
                ]),
            ]);

        $this->mockPropertyProcesser
            ->expects($this->any())
            ->method('hydrate')
            ->willReturnCallback(function ($value) {
                return $value;
            });

        $object = (object)['id' => 1];
        $data   = $processor->hydrate($object);

        $this->assertSame('theDefaultValue', $data->name);
    }

    /**
     * @test
     */
    public function hydrateWillDelegateToValueProcessorWhenPropertyInSchema()
    {
        $processor = $this->createProcessor([$this, 'factory'], 'a', 'b', 'c');

        $this->mockPropertyProcesser
            ->expects($this->exactly(3))
            ->method('hydrate')
            ->withConsecutive([3], [null], [1])
            ->willReturnOnConsecutiveCalls('three', 'two', 'one');

        $actual = $processor->hydrate((object)['a' => 3, 'b' => null, 'c' => 1]);

        $this->assertEquals((object)['a' => 'three', 'b' => 'two', 'c' => 'one'], $actual);
    }

    /**
     * @test
     */
    public function dehydrateWillDelegateToAnyProcessorIfPropertyNotInSchemaAndPropertyExists()
    {
        $processor = $this->createProcessor([$this, 'factory'], 'aInt');

        $object = (object)['aInt' => 1, 'anyProperty' => 'anyValue'];

        $this->mockPropertyProcesser
            ->expects($this->once())
            ->method('dehydrate')
            ->with(1)
            ->willReturnCallback(function ($value) {
                return $value;
            });

        $this->mockAnyProcessor
            ->expects($this->once())
            ->method('dehydrate')
            ->with($object->anyProperty)
            ->willReturnCallback(function ($value) {
                return $value;
            });

        $data = $processor->dehydrate($object);

        $this->assertSame(1, $data->aInt);
        $this->assertObjectHasAttribute('anyProperty', $data);
        $this->assertSame($object->anyProperty, $data->anyProperty);
    }

    /**
     * If property not in schema:
     *   If property value exists: delegate value determination to value processor (`AnyProcessor`)
     *
     * @test
     */
    public function dehydrateWillDelegateToPropertyProcessorIfPropertyInSchemaAndPropertyExists()
    {
        $processor = $this->createProcessor([$this, 'factory'], 'aInt', 'anyProperty');

        $object = (object)['aInt' => 1, 'anyProperty' => 'anyValue'];

        $this->mockPropertyProcesser
            ->expects($this->exactly(2))
            ->method('dehydrate')
            ->withConsecutive([1], [$object->anyProperty])
            ->willReturnCallback(function ($value) {
                return $value;
            });

        $data = $processor->dehydrate($object);

        $this->assertSame(1, $data->aInt);
        $this->assertObjectHasAttribute('anyProperty', $data);
        $this->assertSame($object->anyProperty, $data->anyProperty);
    }

    protected function factory(ObjectSchema $schema)
    {
        /** @var AnyProcessor $anyProcessor */
        $anyProcessor = $this->mockAnyProcessor = $this
            ->getMockBuilder(AnyProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        return new LooseSimpleObjectProcessor($schema, $anyProcessor);
    }
}
