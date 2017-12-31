<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Processors\Scalar;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar\NumberProcessor;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class NumberProcessorTest extends BasicScalarTest
{
    protected function setUp()
    {
        $this->processor = new NumberProcessor($this->createSchema(Schema::TYPE_NUMBER, 1.0));
    }

    /**
     * @return array
     */
    public static function valueProvider()
    {
        return [
            ['1.0', 1.0],
            ['2', 2],
            [1.0, 1.0],
            [2, 2],
        ];
    }
}
