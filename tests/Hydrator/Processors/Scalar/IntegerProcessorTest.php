<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Processors\Scalar;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Exception\UnsupportedException;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar\IntegerProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class IntegerProcessorTest extends TestCase
{
    /**
     * @test
     */
    public function willCastIntStringToInt()
    {
        $this->assertSame(
            1,
            (new IntegerProcessor(new ScalarSchema((object)['type' => Schema::TYPE_INT])))->hydrate('1.0')
        );
    }

    /**
     * @test
     */
    public function willHydrateDefault()
    {
        $processor = new IntegerProcessor(
            new ScalarSchema((object)[
                'type'   => Schema::TYPE_INT,
                'default' => -1
            ])
        );
        $this->assertSame(-1, $processor->hydrate(null));
    }

    /**
     * @test
     */
    public function constructorOnInt64SchemaWhenNotSupported()
    {
        $this->expectException(UnsupportedException::class);

        new IntegerProcessor(
            new ScalarSchema((object)[
                'type'   => Schema::TYPE_INT,
                'format' => Schema::FORMAT_INT64,
            ]),
            true
        );
    }

    /**
     * @test
     */
    public function hydrateWillFailOnIntWhenTooLargeStringValue()
    {
        $this->expectException(UnsupportedException::class);

        $processor = new IntegerProcessor(
            new ScalarSchema((object)['type' => Schema::TYPE_INT]),
            true
        );

        $this->expectException(UnsupportedException::class);

        $processor->hydrate((string)PHP_INT_MAX . 1);
    }

    /**
     * @test
     */
    public function willFailOnIntWhenTooLargeFloatValue()
    {
        $processor = new IntegerProcessor(
            new ScalarSchema((object)['type' => Schema::TYPE_INT]),
            true
        );

        $this->expectException(UnsupportedException::class);

        $processor->hydrate(((float)PHP_INT_MAX) * 2);
    }
}

