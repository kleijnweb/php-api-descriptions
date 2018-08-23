<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Processors;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\AnySchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ArraySchema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\ArrayProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Processor;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ArrayProcessorTest extends TestCase
{
    /**
     * @var ArrayProcessor
     */
    private $processor;

    protected function setUp()
    {
        $this->processor = new ArrayProcessor(new ArraySchema((object)[], new AnySchema()));
    }

    /**
     * @test
     */
    public function hydrateWillDelegateToItemsProcessor()
    {
        /** @var Processor $parent */
        $mockParent = $parent = $this->getMockBuilder(Processor::class)->disableOriginalConstructor()->getMock();
        $this->processor->setItemsProcessor($parent);

        $mockParent
            ->expects($this->exactly(3))
            ->method('hydrate')
            ->withConsecutive([3], [2], [1])
            ->willReturnOnConsecutiveCalls('three', 'two', 'one');

        $actual = $this->processor->hydrate([3, 2, 1]);

        $this->assertSame(['three', 'two', 'one'], $actual);
    }

    /**
     * @test
     */
    public function hydrateWillReturnDefaultWhenValueIsNull()
    {
        $processor = new ArrayProcessor(new ArraySchema((object)['default' => [1, 2, 3]], new AnySchema()));

        /** @var Processor $parent */
        $mockParent = $parent = $this->getMockBuilder(Processor::class)->disableOriginalConstructor()->getMock();
        $processor->setItemsProcessor($parent);

        $mockParent
            ->expects($this->exactly(3))
            ->method('hydrate')
            ->withConsecutive([1], [2], [3])
            ->willReturnOnConsecutiveCalls('three', 'two', 'one');

        $actual = $processor->hydrate(null);

        $this->assertSame(['three', 'two', 'one'], $actual);
    }

    /**
     * @test
     */
    public function hydrateWillReturnNullWhenValueIsNullAndNoDefault()
    {
        /** @var Processor $parent */
        $mockParent = $parent = $this->getMockBuilder(Processor::class)->disableOriginalConstructor()->getMock();
        $this->processor->setItemsProcessor($parent);

        $mockParent
            ->expects($this->never())
            ->method('hydrate');

        $this->assertNull($this->processor->hydrate(null));
    }

    /**
     * @test
     */
    public function dehydrateWillWillDelegateToItemsProcessor()
    {
        /** @var Processor $parent */
        $mockParent = $parent = $this->getMockBuilder(Processor::class)->disableOriginalConstructor()->getMock();
        $this->processor->setItemsProcessor($parent);

        $mockParent
            ->expects($this->exactly(3))
            ->method('dehydrate')
            ->withConsecutive([3], [2], [1])
            ->willReturnOnConsecutiveCalls('three', 'two', 'one');

        $actual = $this->processor->dehydrate([3, 2, 1]);

        $this->assertSame(['three', 'two', 'one'], $actual);
    }
}
