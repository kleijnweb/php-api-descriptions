<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Request;

use KleijnWeb\PhpApi\Descriptions\Description\DescriptionFactory;
use KleijnWeb\PhpApi\Descriptions\Description\Repository;
use KleijnWeb\PhpApi\Descriptions\MessageValidator;
use KleijnWeb\PhpApi\Descriptions\Tests\Mixins\HttpMessageMockingMixin;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class MessageValidatorTest extends TestCase
{
    use HttpMessageMockingMixin;

    /**
     * @test
     * @group integration
     */
    public function canValidateUsingOpenApiDescription()
    {
        $validator = new MessageValidator(
            (new Repository())
                ->get('tests/definitions/openapi/petstore.yml')
        );

        $path    = '/pets';
        $body    = (object)[
            'name'      => 'Fido',
            'photoUrls' => [],
        ];
        $request = $this->mockRequest($path, [], [], $body, 'POST');


        /** @var ServerRequestInterface $request */
        $result = $validator->validateRequest($request, $path);

        $this->assertTrue($result->isValid());

        $response = $this->mockResponse(200);
        $body     = (object)[];
        $result   = $validator->validateResponse($body, $request, $response, $path);

        $this->assertTrue($result->isValid());
    }

    /**
     * @test
     * @group integration
     */
    public function canValidateUsingRamlDescription()
    {
        $validator = new MessageValidator(
            (new Repository())
                ->setFactory(new DescriptionFactory(DescriptionFactory::BUILDER_RAML))
                ->get('tests/definitions/raml/mobile-order-api/api.raml')
        );

        $path    = '/orders';
        $request = $this->mockRequest($path, [
            'userId' => '1964401a-a8b3-40c1-b86e-d8b9f75b5842',
        ]);

        /** @var ServerRequestInterface $request */
        $result = $validator->validateRequest($request, $path);

        $this->assertTrue($result->isValid());

        $response = $this->mockResponse(200);
        $body     = (object)[];
        $result   = $validator->validateResponse($body, $request, $response, $path);

        $this->assertTrue($result->isValid());
    }
}
