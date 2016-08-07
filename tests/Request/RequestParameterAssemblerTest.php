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
        $params    = [
            'foo' => 'bar',
            'baz' => 1
        ];
        $message   = $this->mockRequest('/foo', $params);
        $operation = $this->createOperation(
            '/foo',
            [
                'foo' => ['getIn' => Parameter::IN_QUERY],
                'baz' => ['getIn' => Parameter::IN_QUERY],
            ]
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
        $params    = [
            'foo' => 'bar',
            'baz' => '1'
        ];
        $message   = $this->mockRequest('/foo/bar/baz/1');
        $operation = $this->createOperation(
            '/foo/{foo}/baz/{baz}',
            [
                'foo' => ['getIn' => Parameter::IN_PATH],
                'baz' => ['getIn' => Parameter::IN_PATH],
            ]
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
        $params    = [
            'foo' => 'bar',
            'baz' => '1'
        ];
        $message   = $this->mockRequest('/foo', [], ['X-Foo' => 'bar', 'baz' => '1']);
        $operation = $this->createOperation(
            '/foo',
            [
                'foo' => ['getIn' => Parameter::IN_HEADER],
                'baz' => ['getIn' => Parameter::IN_HEADER],
            ]
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
        $params    = [
            'baz' => '1'
        ];
        $message   = $this->mockRequest('/foo', [], ['baz' => '1']);
        $operation = $this->createOperation(
            '/foo',
            [
                'foo' => ['getIn' => Parameter::IN_HEADER],
                'baz' => ['getIn' => Parameter::IN_HEADER],
            ]
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
        $body = (object)['baz' => '1'];

        $message = $this->mockRequest('/foo', [], [], $body);
        $operation = $this->createOperation(
            '/foo',
            [
                'foo' => ['getIn' => Parameter::IN_BODY],
            ]
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
    private function mockRequest(
        string $path,
        array $query = [],
        array $headers = [],
        \stdClass $body = null
    ): ServerRequestInterface
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

    /**
     * @param array $parameterStubs
     *
     * @return Operation
     */
    private function createOperation(string $path, array $parameterStubs = []): Operation
    {
        $operationMock = $this->getMockBuilder(Operation::class)->getMock();
        $operationMock->expects($this->once())->method('getPath')->willReturn($path);
        $parameterMocks = [];

        foreach ($parameterStubs as $parameterName => $stubs) {
            $parameterMock = $this->getMockBuilder(Parameter::class)->getMock();
            $parameterMock->expects($this->any())->method('getName')->willReturn($parameterName);

            foreach ($stubs as $methodName => $value) {
                $parameterMock->expects($this->any())->method($methodName)->willReturn($value);
            }
            $parameterMocks[$parameterName] = $parameterMock;
        }
        $operationMock->expects($this->any())->method('getParameters')->willReturn($parameterMocks);

        return $operationMock;
    }
}
