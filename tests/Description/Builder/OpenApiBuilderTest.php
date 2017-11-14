<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Builder;

use KleijnWeb\PhpApi\Descriptions\Description\Builder\OpenApiBuilder;
use KleijnWeb\PhpApi\Descriptions\Description\Description;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\Loader\DefinitionLoader;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\RefResolver\RefResolver;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Document;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
abstract class OpenApiBuilderTest extends TestCase
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

    public function testIsSerializable()
    {
        self::assertEquals(unserialize(serialize($this->description)), $this->description);
    }

    public function testCanGetRequestSchema()
    {
        foreach ($this->description->getPaths() as $path) {
            foreach ($path->getOperations() as $operation) {
                $actual = $this->description->getRequestSchema($operation->getPath(), $operation->getMethod());
                self::assertInstanceOf(Schema::class, $actual);
                $definition = $actual->getDefinition();
                foreach ($operation->getParameters() as $parameter) {
                    self::assertObjectHasAttribute($parameter->getName(), $definition->properties);
                }
            }
        }
    }

    public function testCanGetResponseSchema()
    {
        foreach ($this->description->getPaths() as $path) {
            foreach ($path->getOperations() as $operation) {
                foreach ($operation->getResponses() as $response) {
                    $actual = $this->description->getResponseSchema(
                        $operation->getPath(),
                        $operation->getMethod(),
                        $response->getCode()
                    );
                    self::assertInstanceOf(Schema::class, $actual);
                }
            }
        }
    }
}
