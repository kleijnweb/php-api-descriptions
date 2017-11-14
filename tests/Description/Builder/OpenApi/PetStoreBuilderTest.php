<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Builder\OpenApi;

use KleijnWeb\PhpApi\Descriptions\Description\ComplexType;
use KleijnWeb\PhpApi\Descriptions\Description\Parameter;
use KleijnWeb\PhpApi\Descriptions\Description\Response;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use KleijnWeb\PhpApi\Descriptions\Tests\Description\Builder\OpenApiBuilderTest;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class PetStoreBuilderTest extends OpenApiBuilderTest
{
    protected function setUp()
    {
        $this->setUpDescription('tests/definitions/openapi/petstore.yml');
    }

    public function testHasComplexTypes()
    {
        /** @var ComplexType[] $types */
        $types = [];
        foreach ($this->description->getComplexTypes() as $type) {
            self::assertInstanceOf(ComplexType::class, $type);
            $types[$type->getName()] = $type;
        }
        $expected = [
            'Category',
            'Tag',
            'Pet',
            'Order',
            'User',
        ];
        self::assertSame($expected, array_keys($types));
        /** @var ObjectSchema $propertySchema */
        $propertySchema = $types['Pet']->getSchema()->getPropertySchema('category');
        self::assertSame($propertySchema->getComplexType(), $types['Category']);
        self::assertTrue($types['Pet']->getSchema()->getPropertySchema('category')->isType($types['Category']));
    }

    public function testUnknownPathThrowsException()
    {
        self::expectException(\InvalidArgumentException::class);

        $this->description->getPath('foo');
    }

    public function testCanGetCollectionFormatFromParameter()
    {
        self::assertSame(
            'multi',
            $this->description
                ->getPath('/pets/findByStatus')
                ->getOperation('get')
                ->getParameter('status')
                ->getCollectionFormat()
        );
    }

    public function testUnknownMethodThrowsException()
    {
        self::expectException(\InvalidArgumentException::class);

        $this->description->getPath('/pets')->getOperation('get');
    }

    public function testUnknownStatusCodeThrowsException()
    {
        self::expectException(\InvalidArgumentException::class);

        $this->description->getPath('/pets')->getOperation('post')->getResponse(123);
    }

    public function testCanGetDefaultResponse()
    {
        self::assertInstanceOf(
            Response::class,
            $this->description->getPath('/users')->getOperation('post')->getResponse(123)
        );
    }

    public function testCanGetSupportedStatusCodes()
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
                self::assertSame(
                    $map[$operation->getPath()][$operation->getMethod()],
                    $operation->getStatusCodes(),
                    "Mismatch for operation {$operation->getId()}"
                );
            }
        }
    }

    public function testCanGetRequestBodyParameter()
    {
        self::assertInstanceOf(Parameter::class, $this->description->getRequestBodyParameter('/pets', 'post'));
    }

    public function testGetRequestBodyParameterWillReturnNullIfNonExistent()
    {
        self::assertNull($this->description->getRequestBodyParameter('/pets/findByStatus', 'get'));
    }

    public function testGetters()
    {
        $map = [
            'getDocument' => $this->document,
            'getSchemes'  => ['http'],
            'getHost'     => 'petstore.swagger.io'

        ];
        foreach ($map as $methodName => $value) {
            self::assertSame($value, $this->description->$methodName());
        }
    }
}
