<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Description\Standard\Raml;

use KleijnWeb\ApiDescriptions\Description\Operation;
use KleijnWeb\ApiDescriptions\Description\Path;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RamlPath extends Path
{
    private static $methodNames = [
        'get',
        'patch',
        'put',
        'post',
        'delete',
        'options',
        'head'
    ];

    /**
     * Path constructor.
     *
     * @param string    $path
     * @param \stdClass $definition
     * @param array     $pathParameters
     */
    public function __construct(string $path, \stdClass $definition, array $pathParameters = [])
    {
        $this->path = $path;

        foreach (self::$methodNames as $method) {
            if (isset($definition->$method)) {
                $this->operations[$method] = $this->createOperation($method, $definition->$method);
            }
        }

        $this->pathParameters = array_merge($pathParameters, RamlOperation::extractParameters($definition));
    }

    /**
     * @param string    $method
     * @param \stdClass $operationDefinition
     *
     * @return Operation
     */
    protected function createOperation(string $method, \stdClass $operationDefinition): Operation
    {
        return new RamlOperation(
            $operationDefinition,
            $this->getPath(),
            $method,
            $this->pathParameters
        );
    }
}
