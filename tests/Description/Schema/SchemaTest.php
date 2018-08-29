<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Schema;

use KleijnWeb\PhpApi\Descriptions\Description\ComplexType;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Types\Pet;
use KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Types\Tag;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SchemaTest extends TestCase
{
    /**
     * @test
     */
    public function cannotChangeComplexType()
    {
        $schema = new ObjectSchema((object)[]);
        $schema->setComplexType(new ComplexType('Pet', $schema, Pet::class));
        $this->expectException(\LogicException::class);
        $schema->setComplexType(new ComplexType('Tag', $schema, Tag::class));
    }

    /**
     * @test
     * @see http://json-schema.org/latest/json-schema-validation.html#rfc.section.10
     */
    public function willSetAnnotations()
    {
        $definition = (object)[
            'default'     => 'a',
            'title'       => 'aTitle',
            'description' => 'aDescription',
            'readOnly'    => true,
            'writeOnly'   => true,
            'examples' => [1, 2, 3]
        ];

        $schema = new ScalarSchema($definition);

        $this->assertSame($definition->default, $schema->getDefault());
        $this->assertSame($definition->title, $schema->getTitle());
        $this->assertSame($definition->description, $schema->getDescription());
        $this->assertSame($definition->readOnly, $schema->isReadOnly());
        $this->assertSame($definition->writeOnly, $schema->isWriteOnly());
        $this->assertSame($definition->examples, $schema->getExamples());
    }
}
