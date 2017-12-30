<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Processors\Scalar;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\DateTimeSerializer;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar\DateTimeProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class DateTimeProcessorTest extends TestCase
{
    /**
     * @var DateTimeProcessor
     */
    private $processor;

    /**
     * @var MockObject
     */
    private $serializer;

    protected function setUp()
    {
        /** @var DateTimeSerializer $serializer */
        $serializer = $this->serializer = $this->getMockBuilder(DateTimeSerializer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor = new DateTimeProcessor(new ScalarSchema((object)[]), $serializer);
    }

    /**
     * @test
     */
    public function hydrateWillDelegateToUnserialize()
    {
        $value = $this->getMockForAbstractClass(\DateTime::class);

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($value);

        $this->processor->hydrate($value);
    }

    /**
     * @test
     */
    public function dehydrateWillDelegateToSerialize()
    {
        $value = $this->getMockForAbstractClass(\DateTime::class);

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($value);

        $this->processor->dehydrate($value);
    }
}

