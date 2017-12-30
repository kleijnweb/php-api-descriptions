<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Processors\Object;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object\StrictSimpleObjectProcessor;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class StrictSimpleObjectProcessorTest extends SimpleObjectProcessorTest
{
    /**
     * If property not in schema: omit.
     *
     * @test
     */
    public function hydrateWillOmitWhenPropertyNotInSchema()
    {
        $processor = $this->createProcessor([$this, 'factory'], 'aInt');

        $object = (object)['aInt' => 1, 'nullProperty' => null];

        $this->mockPropertyProcesser
            ->expects($this->any())
            ->method('hydrate')
            ->willReturnCallback(function ($value) {
                return $value;
            });

        $data = $processor->hydrate($object);

        $this->assertSame(1, $data->aInt);
        $this->assertObjectNotHasAttribute('nullProperty', $data);
    }

    /**
     * Else: delegate value determination to property processor
     *
     * @test
     */
    public function hydrateWillDelegateWhenPropertyInSchema()
    {
        $processor = $this->createProcessor([$this, 'factory'], 'aInt', 'nullProperty');

        $object = (object)['aInt' => 1, 'nullProperty' => null];

        $this->mockPropertyProcesser
            ->expects($this->any())
            ->method('hydrate')
            ->willReturnCallback(function ($value) {
                return $value;
            });

        $data = $processor->hydrate($object);

        $this->assertSame(1, $data->aInt);
        $this->assertObjectHasAttribute('nullProperty', $data);
        $this->assertNull($data->nullProperty);
    }

    /**
     * If property not in schema: fail
     *
     * @test
     */
    public function dehydrateWillOmitWhenPropertyNotInSchema()
    {
        $processor = $this->createProcessor([$this, 'factory'], 'aInt');

        $object = (object)['aInt' => 1, 'nullProperty' => null];

        $this->expectException(\OutOfBoundsException::class);

        $processor->dehydrate($object);
    }

    /**
     * Else: delegate value determination to property processor
     *
     * @test
     */
    public function dehydrateWillDelegateWhenPropertyInSchema()
    {
        $processor = $this->createProcessor(StrictSimpleObjectProcessor::class, 'a', 'b', 'c');

        $this->mockPropertyProcesser
            ->expects($this->exactly(3))
            ->method('dehydrate')
            ->withConsecutive([3], [2], [1])
            ->willReturnOnConsecutiveCalls('three', 'two', 'one');

        $actual = $processor->dehydrate((object)['a' => 3, 'b' => 2, 'c' => 1]);

        $this->assertEquals((object)['a' => 'three', 'b' => 'two', 'c' => 'one'], $actual);
    }

    protected function factory(ObjectSchema $schema)
    {
        return new StrictSimpleObjectProcessor($schema);
    }
}
