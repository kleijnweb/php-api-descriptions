<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Tests\Request;

use KleijnWeb\ApiDescriptions\Description\Operation;
use KleijnWeb\ApiDescriptions\Description\Parameter;
use KleijnWeb\ApiDescriptions\Request\ParameterCoercer;
use KleijnWeb\ApiDescriptions\Request\RequestParameterAssembler;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestParameterAssemblerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RequestParameterAssembler
     */
    private $assembler;

    protected function setUp()
    {
        $coercer = $this->getMockBuilder(ParameterCoercer::class)->disableOriginalConstructor()->getMock();
        $coercer->expects($this->any())->method('coerce')->willReturnCallback(function ($parameter, $value) {
            return $value;
        });

        $this->assembler = new RequestParameterAssembler($coercer);
    }

    /**
     * @test
     */
    public function canAssembleQueryParameters()
    {
        $params  = [
            'foo' => 'bar',
            'baz' => 1
        ];
        $message = $this->mockRequest('/foo', $params);

        $operation = new Operation(
            (object)[
                'parameters' => [
                    (object)['name' => 'foo', 'in' => Parameter::IN_QUERY],
                    (object)['name' => 'baz', 'in' => Parameter::IN_QUERY],
                ]
            ],
            '/foo',
            'get'
        );
        /** @var ServerRequestInterface $message */
        $actual = $this->assembler->getRequestParameters($message, $operation);

        $this->assertEquals((object)$params, $actual);
    }

    /**
     * @test
     */
    public function canAssemblePathParameters()
    {
        $params  = [
            'foo' => 'bar',
            'baz' => '1'
        ];
        $message = $this->mockRequest('/foo/bar/baz/1');

        $operation = new Operation(
            (object)[
                'parameters' => [
                    (object)['name' => 'foo', 'in' => Parameter::IN_PATH],
                    (object)['name' => 'baz', 'in' => Parameter::IN_PATH],
                ]
            ],
            '/foo/{foo}/baz/{baz}',
            'get'
        );
        /** @var ServerRequestInterface $message */
        $actual = $this->assembler->getRequestParameters($message, $operation);

        $this->assertEquals((object)$params, $actual);
    }

    /**
     * @test
     */
    public function canAssembleHeaderParameters()
    {
        $params  = [
            'foo' => 'bar',
            'baz' => '1'
        ];
        $message = $this->mockRequest('/foo', [], ['X-Foo' => 'bar', 'baz' => '1']);

        $operation = new Operation(
            (object)[
                'parameters' => [
                    (object)['name' => 'foo', 'in' => Parameter::IN_HEADER],
                    (object)['name' => 'baz', 'in' => Parameter::IN_HEADER],
                ]
            ],
            '/foo',
            'get'
        );
        /** @var ServerRequestInterface $message */
        $actual = $this->assembler->getRequestParameters($message, $operation);

        $this->assertEquals((object)$params, $actual);
    }

    /**
     * @test
     */
    public function willSkipHeaderWithoutValue()
    {
        $params  = [
            'baz' => '1'
        ];
        $message = $this->mockRequest('/foo', [], [ 'baz' => '1']);

        $operation = new Operation(
            (object)[
                'parameters' => [
                    (object)['name' => 'foo', 'in' => Parameter::IN_HEADER],
                    (object)['name' => 'baz', 'in' => Parameter::IN_HEADER],
                ]
            ],
            '/foo',
            'get'
        );
        /** @var ServerRequestInterface $message */
        $actual = $this->assembler->getRequestParameters($message, $operation);

        $this->assertEquals((object)$params, $actual);
    }

    /**
     * @test
     */
    public function willCopyBodyAsIs()
    {
        $body    = (object)['baz' => '1'];

        $message = $this->mockRequest('/foo', [], [], $body);

        $operation = new Operation(
            (object)[
                'parameters' => [
                    (object)['name' => 'foo', 'in' => Parameter::IN_BODY],
                ]
            ],
            '/foo',
            'post'
        );
        /** @var ServerRequestInterface $message */
        $params = $this->assembler->getRequestParameters($message, $operation);
        $actual = $params->foo;

        $this->assertSame($body, $actual);
    }

    /**
     * @param string    $path
     * @param array     $query
     * @param array     $headers
     * @param \stdClass $body
     *
     * @return ServerRequestInterface
     */
    private function mockRequest(string $path, array $query = [], array $headers = [], \stdClass $body = null): ServerRequestInterface
    {
        $message = $this->getMockForAbstractClass(ServerRequestInterface::class);
        $message->expects($this->once())->method('getQueryParams')->willReturn($query);
        $message->expects($this->once())->method('getUri')->willReturnCallback(function () use ($path) {
            $uri = $this->getMockForAbstractClass(UriInterface::class);
            $uri->expects($this->once())->method('getPath')->willReturn($path);

            return $uri;
        });

        $message->expects($this->once())->method('getHeaders')->willReturn($headers);

        if (null !== $body) {
            $message->expects($this->once())->method('getParsedBody')->willReturn($body);
        }


        return $message;
    }
}
