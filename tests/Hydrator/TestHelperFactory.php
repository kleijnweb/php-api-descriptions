<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Hydrator;

use KleijnWeb\PhpApi\Descriptions\Description\ComplexType;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ArraySchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\ClassNameResolver;
use KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Types\Category;
use KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Types\Pet;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class TestHelperFactory
{
    public static function createClassNameResolver(): ClassNameResolver
    {
        return new ClassNameResolver(['KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Types']);
    }

    /**
     * @return ObjectSchema
     */
    public static function createPartialPetSchema(): ObjectSchema
    {
        $tagSchema      = self::createTagSchema();
        $categorySchema = new ObjectSchema((object)[], (object)[]);
        $categorySchema->setComplexType(new ComplexType('Category', $categorySchema));
        $petSchema = new ObjectSchema(
            (object)[],
            (object)[
                'id'       => new ScalarSchema((object)['type' => 'integer']),
                'price'    => new ScalarSchema((object)['type' => 'number', 'default' => 100.0]),
                'label'    => new ScalarSchema((object)['type' => 'string']),
                'category' => $categorySchema,
                'tags'     => new ArraySchema((object)['default' => []], $tagSchema),
                'rating'   => new ObjectSchema((object)[], (object)[
                    'value'   => new ScalarSchema((object)['type' => 'number']),
                    'created' => new ScalarSchema((object)[
                        'type'    => 'string',
                        'format'  => 'date',
                        'default' => 'now',
                    ]),
                ]),
            ]
        );
        $petSchema->setComplexType(new ComplexType('Pet', $petSchema, Pet::class));

        return $petSchema;
    }

    /**
     * @param bool $useComplexTypes
     * @return ObjectSchema
     */
    public static function createFullPetSchema(bool $useComplexTypes = true): ObjectSchema
    {
        $tagSchema      = self::createTagSchema($useComplexTypes);
        $categorySchema = new ObjectSchema(
            (object)[],
            (object)[
                'id'   => new ScalarSchema((object)['type' => 'integer']),
                'name' => new ScalarSchema((object)['type' => 'string']),
            ]
        );

        if ($useComplexTypes) {
            $categorySchema->setComplexType(new ComplexType('Category', $categorySchema, Category::class));
        }

        $petSchema = new ObjectSchema(
            (object)[],
            (object)[
                'id'        => new ScalarSchema((object)['type' => 'integer']),
                'price'     => new ScalarSchema((object)['type' => 'number', 'default' => 100.0]),
                'label'     => new ScalarSchema((object)['type' => 'string']),
                'name'      => new ScalarSchema((object)['type' => 'string']),
                'status'    => new ScalarSchema((object)['type' => 'string']),
                'category'  => $categorySchema,
                'photoUrls' => new ArraySchema(
                    (object)[],
                    new ScalarSchema((object)['type' => Schema::TYPE_STRING])
                ),
                'tags'      => new ArraySchema((object)['default' => []], $tagSchema),
                'rating'    => new ObjectSchema((object)[], (object)[
                    'value'   => new ScalarSchema((object)['type' => 'number']),
                    'created' => new ScalarSchema((object)[
                        'type'    => 'string',
                        'format'  => 'date',
                        'default' => 'now',
                    ]),
                ]),
            ]
        );

        if ($useComplexTypes) {
            $petSchema->setComplexType(new ComplexType('Pet', $petSchema, Pet::class));
        }

        return $petSchema;
    }

    /**
     * @param bool $useComplexTypes
     * @return ObjectSchema
     */
    public static function createTagSchema($useComplexTypes = true): ObjectSchema
    {
        $tagSchema = new ObjectSchema(
            (object)[],
            (object)[
                'id'   => new ScalarSchema((object)['type' => 'integer']),
                'name' => new ScalarSchema((object)['type' => 'string']),
            ]
        );

        if ($useComplexTypes) {
            $tagSchema->setComplexType(new ComplexType('Tag', $tagSchema, Tag::class));
        }

        return $tagSchema;
    }
}
