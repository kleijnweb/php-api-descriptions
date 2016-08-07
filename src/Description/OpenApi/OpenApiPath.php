<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Description\OpenApi;

use KleijnWeb\ApiDescriptions\Description\Operation;
use KleijnWeb\ApiDescriptions\Description\Path;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class OpenApiPath extends Path
{
    /**
     * Path constructor.
     *
     * @param string    $path
     * @param \stdClass $definition
     */
    public function __construct(string $path, \stdClass $definition)
    {
        $this->path = $path;
        $definition = clone $definition;
        if (isset($definition->parameters)) {
            foreach ($definition->parameters as $parameterDefinition) {
                $this->pathParameters[] = new OpenApiParameter($parameterDefinition);
            }
            unset($definition->parameters);
        }

        foreach ($definition as $method => $operationDefinition) {
            $method                    = strtolower($method);
            $this->operations[$method] = $this->createOperation($method, $operationDefinition);
        }
    }

    /**
     * @param string    $method
     * @param \stdClass $operationDefinition
     *
     * @return Operation
     */
    protected function createOperation(string $method, \stdClass $operationDefinition): Operation
    {
        return new OpenApiOperation(
            $operationDefinition,
            $this->getPath(),
            $method,
            $this->pathParameters
        );
    }
}
