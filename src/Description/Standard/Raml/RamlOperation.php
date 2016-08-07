<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Description\Standard\Raml;

use KleijnWeb\ApiDescriptions\Description\Operation;
use KleijnWeb\ApiDescriptions\Description\Parameter;
use KleijnWeb\ApiDescriptions\Description\Schema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RamlOperation extends Operation
{
    /**
     * Operation constructor.
     *
     * @param \stdClass $definition
     * @param string    $path
     * @param string    $method
     * @param array     $pathParameters
     */
    public function __construct(\stdClass $definition, string $path, string $method, array $pathParameters = [])
    {
        $this->path       = $path;
        $this->method     = $method;
        $this->parameters = array_merge($pathParameters, self::extractParameters($definition));


        if (isset($definition->responses)) {
            $hasOkResponse = false;
            foreach ($definition->responses as $code => $responseDefinition) {
                $code                   = (int)$code;
                $this->responses[$code] = new RamlResponse($code, $responseDefinition);
            }
            if (!$hasOkResponse) {
                $this->responses[200] = new RamlResponse(200, (object)[]);
            }
        }

        $schemaDefinition = (object)[];
        if (!count($this->parameters)) {
            $schemaDefinition->type = 'null';
            $this->requestSchema    = Schema::get($schemaDefinition);
        } else {
            $schemaDefinition->type       = 'object';
            $schemaDefinition->required   = [];
            $schemaDefinition->properties = (object)[];

            foreach ($this->parameters as $parameter) {
                if ($parameter->isRequired()) {
                    $schemaDefinition->required[] = $parameter->getName();
                }
                $schemaDefinition->properties->{$parameter->getName()} = $parameter->getSchema()->getDefinition();
            }

            $this->requestSchema = Schema::get($schemaDefinition);
        }
    }

    /**
     * @param \stdClass $definition
     *
     * @return array
     */
    public static function extractParameters(\stdClass $definition)
    {
        $parameters = [];

        if (isset($definition->queryParameters)) {
            foreach ($definition->queryParameters as $name => $parameterDefinition) {
                $parameters[] = new RamlParameter($name, Parameter::IN_QUERY, $parameterDefinition);
            }
        }
        if (isset($definition->uriParameters)) {
            foreach ($definition->uriParameters as $name => $parameterDefinition) {
                $parameters[] = new RamlParameter($name, Parameter::IN_PATH, $parameterDefinition);
            }
        }

        return $parameters;
    }
}
