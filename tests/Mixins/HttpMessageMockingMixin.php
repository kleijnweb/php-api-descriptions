<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Mixins;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
trait HttpMessageMockingMixin
{
    /**
     * @param string    $path
     * @param array     $query
     * @param array     $headers
     * @param \stdClass $body
     *
     * @param string    $method
     *
     * @return ServerRequestInterface
     */
    protected function mockRequest(
        string $path,
        array $query = [],
        array $headers = [],
        \stdClass $body = null,
        string $method = 'GET'
    ): ServerRequestInterface {


        $message = $this->getMockForAbstractClass(ServerRequestInterface::class);
        $message->expects($this->once())->method('getQueryParams')->willReturn($query);
        $message->expects($this->any())->method('getMethod')->willReturn($method);

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
     * @param int $statusCode
     *
     * @return ResponseInterface
     */
    protected function mockResponse(int $statusCode): ResponseInterface
    {
        $message = $this->getMockForAbstractClass(ResponseInterface::class);
        $message->expects($this->once())->method('getStatusCode')->willReturn($statusCode);

        return $message;
    }

    /**
     * @param string $originalClassName
     * @param array  $arguments
     * @param string $mockClassName
     * @param bool   $callOriginalConstructor
     * @param bool   $callOriginalClone
     * @param bool   $callAutoload
     * @param array  $mockedMethods
     * @param bool   $cloneArguments
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    abstract protected function getMockForAbstractClass(
        $originalClassName,
        array $arguments = [],
        $mockClassName = '',
        $callOriginalConstructor = true,
        $callOriginalClone = true,
        $callAutoload = true,
        $mockedMethods = [],
        $cloneArguments = false
    );

    /**
     * @return \PHPUnit_Framework_MockObject_Matcher_Invocation
     */
    abstract protected function once();

    /**
     * @return \PHPUnit_Framework_MockObject_Matcher_Invocation
     */
    abstract protected function any();
}
