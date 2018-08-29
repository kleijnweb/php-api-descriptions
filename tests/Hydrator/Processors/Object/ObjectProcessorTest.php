<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Processors\Object;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object\ObjectProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Processor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
abstract class ObjectProcessorTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $mockPropertyProcessor;

    /**
     * @var Processor
     */
    protected $propertyProcessor;

    protected function setUp()
    {
        $mock                        = $this->createMockPropertyProcessor();
        $this->mockPropertyProcessor = $mock;
        $this->propertyProcessor     = $mock;
    }

    protected function createProcessor($factory, ...$setup): ObjectProcessor
    {
        if (is_string($setup[0])) {
            $propertySchemas = (object)[];

            foreach ($setup as $name) {
                $propertySchemas->$name = new ScalarSchema((object)['type' => Schema::TYPE_STRING]);
            }
            $schema = new ObjectSchema((object)[], $propertySchemas);
        } elseif ($setup[0] instanceof ObjectSchema) {
            $schema = $setup[0];
        } else {
            $schema = new ObjectSchema((object)[], $setup[0]);
        }

        if (is_string($factory)) {
            $factory = function (Schema $schema) use ($factory) {
                return new $factory($schema);
            };
        }

        /** @var ObjectProcessor $processor */
        $processor = $factory($schema);

        foreach ($schema->getPropertySchemas() as $name => $schema) {
            $processor->setPropertyProcessor($name, $this->propertyProcessor);
        }

        return $processor;
    }

    protected function createMockPropertyProcessor(): Processor
    {
        /** @var Processor $processor */
        $processor = $mockObject = $this->getMockBuilder(Processor::class)->disableOriginalConstructor()->getMock();

        return $processor;
    }
}
