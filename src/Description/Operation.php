<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Description\Visitor\VisiteeMixin;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class Operation implements Element
{
    use VisiteeMixin;

    /**
     * @var string
     */
    protected $id;


    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var Parameter[]
     */
    protected $parameters;

    /**
     * @var Schema
     */
    protected $requestSchema;

    /**
     * @var Response[]
     */
    protected $responses;

    /**
     * @var array
     */
    private $extensions;

    /**
     * Operation constructor.
     *
     * @param string      $id
     * @param string      $path
     * @param string      $method
     * @param Parameter[] $parameters
     * @param Schema      $requestSchema
     * @param Response[]  $responses
     * @param array       $extensions
     */
    public function __construct(
        string $id,
        string $path,
        string $method,
        array $parameters = [],
        Schema $requestSchema = null,
        array $responses = [],
        array $extensions = []
    ) {
        $this->id            = $id;
        $this->path          = $path;
        $this->method        = $method;
        $this->parameters    = $parameters;
        $this->requestSchema = $requestSchema;
        $this->responses     = $responses;
        $this->extensions    = $extensions;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getExtension(string $name)
    {
        return isset($this->extensions[$name]) ? $this->extensions[$name] : null;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return int[]
     */
    public function getStatusCodes(): array
    {
        return array_keys($this->responses);
    }

    /**
     * @param int $code
     *
     * @return Response
     */
    public function getResponse(int $code): Response
    {
        if (!isset($this->responses[$code])) {
            if (isset($this->responses[0])) {
                // Return default response
                return $this->responses[0];
            }
            throw new \InvalidArgumentException(
                "Operation '{$this->getId()}' does not have a definition for status '$code'" .
                " (has " . implode(', ', array_keys($this->responses)) . ')'
            );
        }

        return $this->responses[$code];
    }

    /**
     * @return Response[]
     */
    public function getResponses()
    {
        return array_values($this->responses);
    }

    /**
     * @return Schema
     */
    public function getRequestSchema(): Schema
    {
        return $this->requestSchema;
    }

    /**
     * @return bool
     */
    public function hasParameters(): bool
    {
        return (bool)count($this->parameters);
    }

    /**
     * @return  Parameter[]
     */
    public function getParameters(): array
    {
        return $this->hasParameters() ? $this->parameters : [];
    }

    /**
     * @param string $name
     *
     * @return Parameter
     */
    public function getParameter(string $name): Parameter
    {
        foreach ($this->getParameters() as $parameter) {
            if ($parameter->getName() === $name) {
                return $parameter;
            }
        }
        throw new \OutOfBoundsException("Parameter '$name' does not exist");
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }
}
