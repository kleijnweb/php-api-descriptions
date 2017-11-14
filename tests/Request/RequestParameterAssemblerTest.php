<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\PhpApi\Descriptions\Tests\Request;

use KleijnWeb\PhpApi\Descriptions\Description\Operation;
use KleijnWeb\PhpApi\Descriptions\Description\Parameter;
use KleijnWeb\PhpApi\Descriptions\Request\ParameterCoercer;
use KleijnWeb\PhpApi\Descriptions\Request\RequestParameterAssembler;
use KleijnWeb\PhpApi\Descriptions\Tests\Mixins\HttpMessageMockingMixin;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestParameterAssemblerTest extends TestCase
{
    use HttpMessageMockingMixin;

    /**
     * @var RequestParameterAssembler
     */
    private $assembler;

    protected function setUp()
    {
        $coercer = $this->getMockBuilder(ParameterCoercer::class)->disableOriginalConstructor()->getMock();
        $coercer->expects(self::any())->method('coerce')->willReturnCallback(function ($parameter, $value) {
            return $value;
        });

        $this->assembler = new RequestParameterAssembler($coercer);
    }

    public function testCanAssembleQueryParameters()
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

        self::assertEquals((object)$params, $actual);
    }

    public function testCanAssemblePathParameters()
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

        self::assertEquals((object)$params, $actual);
    }

    public function testCanAssembleHeaderParameters()
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

        self::assertEquals((object)$params, $actual);
    }

    public function testWillSkipHeaderWithoutValue()
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

        self::assertEquals((object)$params, $actual);
    }

    public function testWillCopyBodyAsIs()
    {
        $body = (object)['baz' => '1'];

        $message   = $this->mockRequest('/foo', [], [], $body);
        $operation = $this->createOperation(
            '/foo',
            [
                'foo' => ['getIn' => Parameter::IN_BODY],
            ]
        );
        /** @var ServerRequestInterface $message */
        $params = $this->assembler->getRequestParameters($message, $operation);
        $actual = $params->foo;

        self::assertSame($body, $actual);
    }

    /**
     * @param string $path
     * @param array $parameterStubs
     * @return Operation
     */
    private function createOperation(string $path, array $parameterStubs = []): Operation
    {
        $operationMock = $this->getMockBuilder(Operation::class)->disableOriginalConstructor()->getMock();
        $operationMock->expects(self::once())->method('getPath')->willReturn($path);
        $parameterMocks = [];

        foreach ($parameterStubs as $parameterName => $stubs) {
            $parameterMock = $this->getMockBuilder(Parameter::class)->disableOriginalConstructor()->getMock();
            $parameterMock->expects(self::any())->method('getName')->willReturn($parameterName);

            foreach ($stubs as $methodName => $value) {
                $parameterMock->expects(self::any())->method($methodName)->willReturn($value);
            }
            $parameterMocks[$parameterName] = $parameterMock;
        }
        $operationMock->expects(self::any())->method('getParameters')->willReturn($parameterMocks);

        return $operationMock;
    }
}
