<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description;

use KleijnWeb\PhpApi\Descriptions\Description\Visitor\VisiteeMixin;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class Path implements Element
{
    use VisiteeMixin;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var Operation[]
     */
    protected $operations = [];

    /**
     * @var Parameter[]
     */
    protected $pathParameters = [];

    /**
     * @var array
     */
    private $extensions;

    /**
     * Path constructor.
     *
     * @param string      $path
     * @param Operation[] $operations
     * @param Parameter[] $pathParameters
     * @param array       $extensions
     */
    public function __construct($path, array $operations, array $pathParameters = [], array $extensions = [])
    {
        $this->path           = $path;
        $this->operations     = $operations;
        $this->pathParameters = $pathParameters;
        $this->extensions     = $extensions;
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
     * @return Operation[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * @param string $method
     *
     * @return Operation
     */
    public function getOperation(string $method): Operation
    {
        $method = strtolower($method);

        if (!isset($this->operations[$method])) {
            throw new \InvalidArgumentException(
                "Path '{$this->getPath()}' does not support '$method'" .
                " (supports " . implode(', ', array_keys($this->operations)) . ')'
            );
        }

        return $this->operations[$method];
    }

    /**
     * @return Parameter[]
     */
    public function getPathParameters(): array
    {
        return $this->pathParameters;
    }
}
