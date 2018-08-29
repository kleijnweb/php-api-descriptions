<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Processors\Scalar;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar\ScalarProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
abstract class BasicScalarTest extends TestCase
{
    /**
     * @var ScalarProcessor
     */
    protected $processor;

    /**
     * @var ScalarProcessor
     */
    private $default;

    /**
     * @test
     * @dataProvider  valueProvider
     *
     * @param int|float $value
     * @param int|float $hydrated
     */
    public function willHydrateAsExpected($value, $hydrated)
    {
        $this->assertSame($hydrated, $this->processor->hydrate($value));
    }

    /**
     * @test
     * @dataProvider  valueProvider
     *
     * @param mixed $value
     */
    public function dehydrateWillAlwaysReturnValueAsIs($value)
    {
        $this->assertSame($value, $this->processor->dehydrate($value));
    }

    /**
     * @test
     */
    public function willHydrateDefault()
    {
        $this->assertSame($this->default, $this->processor->hydrate(null));
    }

    protected function createSchema(string $type, $default): ScalarSchema
    {
        return new ScalarSchema(
            (object)[
                'type'    => $type,
                'default' => $this->default = $default,
            ]
        );
    }
}
