<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Schema\Validator;

use KleijnWeb\PhpApi\Descriptions\Description\Schema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator\JustinRainbowSchemaValidatorAdapter;
use PHPUnit\Framework\TestCase;

class JustinRainbowSchemaValidatorAdapterTest extends TestCase
{
    public function testWillContainIndexedErrors()
    {
        $factory = new Schema\SchemaFactory();

        $validator = new JustinRainbowSchemaValidatorAdapter();
        $result    = $validator->validate(
            $factory->create((object)[
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
                        'type' => 'integer',
                    ]
                ]
            ]),
            (object)['foo' => (object)['bar' => 1]]
        );
        self::assertFalse($result->isValid());

        $expected = [
            'bar'     => 'The property bar is required',
            'foo.bar' => 'Must have a minimum value of 10',
        ];
        self::assertSame($expected, $result->getErrorMessages());
    }

    public function testCanForceNoAdditionalProperties()
    {
        $factory = new Schema\SchemaFactory();

        $validator = new JustinRainbowSchemaValidatorAdapter();
        $result    = $validator->validate(
            $factory->create((object)[
                'type'       => 'object',
                'properties' => (object)[
                    'foo' => (object)['type' => 'integer'],
                ]
            ]),
            (object)['bar' => 1],
            true
        );
        self::assertFalse($result->isValid());

        $expected = ['' => 'The property bar is not defined and the definition does not allow additional properties'];

        self::assertSame($expected, $result->getErrorMessages());
    }

    public function testCanDefaultToRequireAll()
    {
        $factory = new Schema\SchemaFactory();

        $validator = new JustinRainbowSchemaValidatorAdapter();
        $result    = $validator->validate(
            $factory->create((object)[
                'type'       => 'object',
                'properties' => (object)[
                    'foo' => (object)['type' => 'integer'],
                    'bar' => (object)['type' => 'integer',]
                ]
            ]),
            (object)['foo' => 1],
            false,
            true
        );
        self::assertFalse($result->isValid());

        $expected = ['bar' => 'The property bar is required'];

        self::assertSame($expected, $result->getErrorMessages());
    }
}
