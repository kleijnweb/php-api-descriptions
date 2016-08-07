<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Tests\Description\Standard\Raml;

use KleijnWeb\ApiDescriptions\Description\Description;
use KleijnWeb\ApiDescriptions\Description\Schema;
use KleijnWeb\ApiDescriptions\Description\Standard\Raml\RamlDescription;
use KleijnWeb\ApiDescriptions\Description\Standard\Raml\RamlOperation;
use KleijnWeb\ApiDescriptions\Description\Standard\Raml\RamlParameter;
use KleijnWeb\ApiDescriptions\Description\Standard\Raml\RamlPath;
use KleijnWeb\ApiDescriptions\Description\Standard\Raml\RamlResponse;
use KleijnWeb\ApiDescriptions\Description\Visitor\ClosureVisitor;
use KleijnWeb\ApiDescriptions\Description\Visitor\ClosureVisitorScope;
use KleijnWeb\ApiDescriptions\Document\Definition\Loader\DefinitionLoader;
use KleijnWeb\ApiDescriptions\Document\Definition\RefResolver\RefResolver;
use KleijnWeb\ApiDescriptions\Document\Document;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class DescriptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Description
     */
    private $description;

    /**
     * @var Document
     */
    private $document;

    protected function setUp()
    {
        $uri = 'tests/definitions/raml/mobile-order-api/api.raml';

        $this->description = new RamlDescription(
            $this->document = new Document(
                $uri,
                $definition = (new RefResolver((new DefinitionLoader())->load($uri), $uri))->resolve()
            )
        );
    }

    /**
     * @test
     */
    public function canVisitElements()
    {
        $scope = new class() implements ClosureVisitorScope
        {
        public $types = [];
        };

        $this->description->accept(
            new ClosureVisitor($scope, function ($element) {
                $this->types[get_class($element)] = get_class($element);
            })
        );

        sort($scope->types);

        $expected = [
            Schema::class,
            RamlDescription::class,
            RamlOperation::class,
            RamlParameter::class,
            RamlPath::class,
            RamlResponse::class,
        ];
        $this->assertSame($expected, $scope->types);
    }

    /**
     * @test
     */
    public function willCreatePathObjectsUsingNestedResources()
    {
        $this->description->getPath('/orders/nested');
    }

    /**
     * @test
     */
    public function unknownPathThrowsException()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->description->getPath('foo');
    }

    /**
     * @test
     */
    public function unknownMethodThrowsException()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->description->getPath('/orders')->getOperation('post');
    }

    /**
     * @test
     */
    public function unknownStatusCodeThrowsException()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->description->getPath('/orders')->getOperation('get')->getResponse(123);
    }

    /**
     * @test
     */
    public function canGetSupportedStatusCodes()
    {
        $map = [
            '/orders' => ['get' => [200]],
        ];

        foreach ($this->description->getPaths() as $path) {
            foreach ($path->getOperations() as $operation) {
                $this->assertSame(
                    $map[$operation->getPath()][$operation->getMethod()],
                    $operation->getStatusCodes(),
                    "Mismatch for operation {$operation->getId()}"
                );
            }
        }
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

    /**
     * @test
     */
    public function testGetters()
    {
        $map = [
            'getDocument' => $this->document,
            'getSchemes'  => ['http', 'https'],
            'getHost'     => null

        ];
        foreach ($map as $methodName => $value) {
            $this->assertSame($value, $this->description->$methodName());
        }
    }
}
