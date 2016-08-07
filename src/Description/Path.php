<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Description;

use KleijnWeb\ApiDescriptions\Description\Visitor\VisiteeMixin;

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
     * Path constructor.
     *
     * @param string      $path
     * @param Operation[] $operations
     * @param Parameter[] $pathParameters
     */
    public function __construct($path, array $operations, array $pathParameters)
    {
        $this->path           = $path;
        $this->operations     = $operations;
        $this->pathParameters = $pathParameters;
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
