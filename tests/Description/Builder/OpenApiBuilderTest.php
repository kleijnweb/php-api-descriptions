<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Tests\Description\Builder;

use KleijnWeb\ApiDescriptions\Description\Builder\OpenApiBuilder;
use KleijnWeb\ApiDescriptions\Description\Description;
use KleijnWeb\ApiDescriptions\Description\Document\Definition\Loader\DefinitionLoader;
use KleijnWeb\ApiDescriptions\Description\Document\Definition\RefResolver\RefResolver;
use KleijnWeb\ApiDescriptions\Description\Document\Document;
use KleijnWeb\ApiDescriptions\Description\Schema\Schema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
abstract class OpenApiBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Description
     */
    protected $description;

    /**
     * @var Document
     */
    protected $document;

    /**
     * @param string $uri
     */
    protected function setUpDescription(string $uri)
    {
        $builder = new OpenApiBuilder(
            $this->document = new Document(
                $uri,
                (new RefResolver((new DefinitionLoader())->load($uri), $uri))->resolve()
            )
        );

        $this->description = $builder->build();
    }

    /**
     * @test
     */
    public function isSerializable()
    {
        $this->assertEquals(unserialize(serialize($this->description)), $this->description);
    }

    /**
     * @test
     */
    public function canGetRequestSchema()
    {
        foreach ($this->description->getPaths() as $path) {
            foreach ($path->getOperations() as $operation) {
                $actual = $this->description->getRequestSchema($operation->getPath(), $operation->getMethod());
                $this->assertInstanceOf(Schema::class, $actual);
                $definition = $actual->getDefinition();
                foreach ($operation->getParameters() as $parameter) {
                    $this->assertObjectHasAttribute($parameter->getName(), $definition->properties);
                }
            }
        }
    }

    /**
     * @test
     */
    public function canGetResponseSchema()
    {
        foreach ($this->description->getPaths() as $path) {
            foreach ($path->getOperations() as $operation) {
                foreach ($operation->getResponses() as $response) {
                    $actual = $this->description->getResponseSchema(
                        $operation->getPath(),
                        $operation->getMethod(),
                        $response->getCode()
                    );
                    $this->assertInstanceOf(Schema::class, $actual);
                }
            }
        }
    }
}
