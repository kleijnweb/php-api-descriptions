<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Processors\Scalar;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar\BoolProcessor;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class BoolProcessorTest extends BasicScalarTest
{

    protected function setUp()
    {
        $this->processor = new BoolProcessor($this->createSchema(Schema::TYPE_BOOL, false));
    }

    /**
     * @return array
     */
    public static function valueProvider()
    {
        return [
            ['1.0', true],
            ['2', true],
            [1.0, true],
            [2, true],
            [true, true],
            [false, false],
            ['', false],
            ['0', false],
            ['', false],
        ];
    }
}
