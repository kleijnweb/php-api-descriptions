<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Schema\Validator;

use KleijnWeb\PhpApi\Descriptions\Description\Schema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator\JsonGuardSchemaValidatorAdapter;
use PHPUnit\Framework\TestCase;

class JsonGuardSchemaValidatorAdapterTest extends TestCase
{
    /**
     * @test
     */
    public function willContainIndexedErrors()
    {
        $factory = new Schema\SchemaFactory();

        $validator = new JsonGuardSchemaValidatorAdapter();
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
                                'minimum' => 10,
                            ],
                        ],
                    ],
                    'bar' => (object)[
                        'type' => 'integer',
                    ],
                ],
            ]),
            (object)['foo' => (object)['bar' => 1]]
        );
        $this->assertFalse($result->isValid());

        $expected = [
            '/'        => 'The object must contain the properties ["bar"].',
            '/foo/bar' => 'The number must be at least 10.',
        ];
        $this->assertSame($expected, $result->getErrorMessages());
    }

    /**
     * @test
     */
    public function canForceNoAdditionalProperties()
    {
        $factory = new Schema\SchemaFactory();

        $validator = new JsonGuardSchemaValidatorAdapter();
        $result    = $validator->validate(
            $factory->create((object)[
                'type'       => 'object',
                'properties' => (object)[
                    'foo' => (object)['type' => 'integer'],
                ],
            ]),
            (object)['bar' => 1],
            true
        );
        $this->assertFalse($result->isValid());

        $expected = ['/' => 'The object must not contain additional properties (["bar"]).'];

        $this->assertSame($expected, $result->getErrorMessages());
    }

    /**
     * @test
     */
    public function canDefaultToRequireAll()
    {
        $factory = new Schema\SchemaFactory();

        $validator = new JsonGuardSchemaValidatorAdapter();
        $result    = $validator->validate(
            $factory->create((object)[
                'type'       => 'object',
                'properties' => (object)[
                    'foo' => (object)['type' => 'integer'],
                    'bar' => (object)['type' => 'integer',],
                ],
            ]),
            (object)['foo' => 1],
            false,
            true
        );
        $this->assertFalse($result->isValid());

        $expected = ['/' => 'The object must contain the properties ["bar"].'];

        $this->assertSame($expected, $result->getErrorMessages());
    }
}
