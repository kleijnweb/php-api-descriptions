<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Builder;

use KleijnWeb\PhpApi\Descriptions\Description\Builder\RamlBuilder;
use KleijnWeb\PhpApi\Descriptions\Description\Description;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\Loader\DefinitionLoader;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\RefResolver\RefResolver;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Document;
use KleijnWeb\PhpApi\Descriptions\Description\Operation;
use KleijnWeb\PhpApi\Descriptions\Description\Parameter;
use KleijnWeb\PhpApi\Descriptions\Description\Path;
use KleijnWeb\PhpApi\Descriptions\Description\Response;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\AnySchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Visitor\ClosureVisitor;
use KleijnWeb\PhpApi\Descriptions\Description\Visitor\ClosureVisitorScope;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RamlBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Description
     */
    protected $description;

    /**
     * @var Document
     */
    private $document;

    protected function setUp()
    {
        $uri = 'tests/definitions/raml/mobile-order-api/api.raml';

        $builder = new RamlBuilder(
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
            ObjectSchema::class,
            ScalarSchema::class,
            Description::class,
            Operation::class,
            Parameter::class,
            Path::class,
            Response::class,
            AnySchema::class
        ];

        sort($expected);

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
    public function canGetParametersFromOperation()
    {
        $parameters = $this->description->getPath('/orders')->getOperation('get')->getParameters();
        $this->assertCount(1, $parameters);
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
    public function testGetters()
    {
        $map = [
            'getDocument' => $this->document,
            'getSchemes'  => ['http', 'https'],
            'getHost'     => ''

        ];
        foreach ($map as $methodName => $value) {
            $this->assertSame($value, $this->description->$methodName());
        }
    }
}
