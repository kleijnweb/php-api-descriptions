<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Tests\Description\Schema\Validator;

use KleijnWeb\ApiDescriptions\Description\Schema;
use KleijnWeb\ApiDescriptions\Description\Schema\Validator\JustinRainbowSchemaValidatorAdapter;

class JustinRainbowSchemaValidatorAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function willContainIndexedErrors()
    {
        $validator = new JustinRainbowSchemaValidatorAdapter();
        $result    = $validator->validate(
            Schema::get((object)[
                'type'       => 'object',
                'required'   => ['foo', 'bar'],
                'properties' => (object)[
                    'foo' => (object)[
                        'type'       => 'object',
                        'properties' => (object)[
                            'bar' => (object)[
                                'type'    => 'integer',
                                'minimum' => 10
                            ]
                        ]
                    ],
                    'bar' => (object)[
                        'type' => 'int',
                    ]
                ]
            ]),
            (object)[
                'foo' => (object)[
                    'bar' => 1
                ]
            ]
        );
        $this->assertFalse($result->isValid());

        $expected = [
            'bar'     => 'The property bar is required',
            'foo.bar' => 'Must have a minimum value of 10',
        ];
        $this->assertSame($expected, $result->getErrorMessages());
    }
}
