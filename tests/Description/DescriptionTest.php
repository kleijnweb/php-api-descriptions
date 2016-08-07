<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Tests\Description;

use KleijnWeb\ApiDescriptions\Description\Description;
use KleijnWeb\ApiDescriptions\Description\Operation;
use KleijnWeb\ApiDescriptions\Description\Parameter;
use KleijnWeb\ApiDescriptions\Description\Path;
use KleijnWeb\ApiDescriptions\Description\Response;
use KleijnWeb\ApiDescriptions\Description\Schema;
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
        $uri = 'tests/definitions/openapi/petstore.yml';

        $this->description = new Description(
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
            Description::class,
            Operation::class,
            Parameter::class,
            Path::class,
            Response::class,
            Schema::class,
        ];
        $this->assertSame($expected, $scope->types);
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

        $this->description->getPath('/pets')->getOperation('get');
    }

    /**
     * @test
     */
    public function unknownStatusCodeThrowsException()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->description->getPath('/pets')->getOperation('post')->getResponse(123);
    }

    /**
     * @test
     */
    public function canGetDefaultResponse()
    {
        $this->assertInstanceOf(
            Response::class,
            $this->description->getPath('/users')->getOperation('post')->getResponse(123)
        );
    }

    /**
     * @test
     */
    public function canGetSupportedStatusCodes()
    {
        $map = [
            '/pets'                     => ['post' => [405, 200], 'put' => [405, 404, 400, 200]],
            '/pets/findByStatus'        => ['get' => [200, 400]],
            '/pets/findByTags'          => ['get' => [200, 400]],
            '/pets/{petId}'             => ['get' => [404, 200, 400], 'post' => [405, 200], 'delete' => [400, 200]],
            '/pets/{petId}/uploadImage' => ['post' => [200]],
            '/stores/inventory'         => ['get' => [200]],
            '/stores/order'             => ['post' => [200, 400]],
            '/stores/order/{orderId}'   => ['get' => [404, 200, 400], 'delete' => [404, 400, 200]],
            '/users'                    => ['post' => [0]],
            '/users/createWithArray'    => ['post' => [0]],
            '/users/createWithList'     => ['post' => [0]],
            '/users/login'              => ['get' => [200, 400]],
            '/users/logout'             => ['get' => [0]],
            '/users/{username}'         => [
                'get'    => [404, 200, 400],
                'put'    => [404, 400, 200],
                'delete' => [404, 400, 200]
            ],
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
            'getSchemes'  => ['http'],
            'getHost'     => 'petstore.swagger.io'

        ];
        foreach ($map as $methodName => $value) {
            $this->assertSame($value, $this->description->$methodName());
        }
    }
}
