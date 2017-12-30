<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Processors;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\AnySchema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\DateTimeSerializer;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\AnyProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class AnyProcessorTest extends TestCase
{
    /**
     * @var AnyProcessor
     */
    private $processor;

    protected function setUp()
    {
        $this->processor = new AnyProcessor(new AnySchema(), new DateTimeSerializer());
    }

    /**
     * @test
     * @dataProvider valueProvider
     *
     * @param int|float $value
     * @param int|float $hydrated
     */
    public function willHydrateAsExpected($value, $hydrated)
    {
        if (is_object($hydrated)) {
            $actual = $this->processor->hydrate($value);
            $this->assertEquals($hydrated, $actual);
            $this->assertNotSame($hydrated, $actual);
        } else {
            $this->assertSame($hydrated, $this->processor->hydrate($value));
        }
    }

    /**
     * @test
     * @dataProvider valueProvider
     *
     * @param int|float $value
     * @param int|float $hydrated
     */
    public function willDehydrateAsPassthrough($value)
    {
        $this->assertSame($value, $this->processor->dehydrate($value));
    }

    /**
     * @return array
     */
    public static function valueProvider(): array
    {
        return [
            [1, 1],
            [1.0, 1.0],
            ['1', '1'],
            [(object)[], new \stdClass()],
            [[1, 2, 3], [1, 2, 3]],
            [(object)['a' => 'b'], (object)['a' => 'b']],
            ['2017', '2017'],
            ['1234567890', '1234567890'],
            ['2017-05-01', new \DateTime('2017-05-01')],
            [($now = new \DateTime)->format(\DateTime::RFC3339_EXTENDED), $now],
        ];
    }
}
