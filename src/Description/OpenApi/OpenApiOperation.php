<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Description\OpenApi;

use KleijnWeb\ApiDescriptions\Description\Operation;
use KleijnWeb\ApiDescriptions\Description\Schema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class OpenApiOperation extends Operation
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
        $this->parameters = $pathParameters;

        if (isset($definition->parameters)) {
            foreach ($definition->parameters as $parameterDefinition) {
                $this->parameters[] = new OpenApiParameter($parameterDefinition);
            }
        }

        if (isset($definition->responses)) {
            $hasOkResponse = false;
            foreach ($definition->responses as $code => $responseDefinition) {
                $code = (string)$code;
                if ($code === 'default' || substr((string)$code, 1) === '1') {
                    $hasOkResponse = true;
                }
                $code                   = (int)$code;
                $this->responses[$code] = new OpenApiResponse($code, $responseDefinition);
            }
            if (!$hasOkResponse) {
                $this->responses[200] = new OpenApiResponse(200, (object)[]);
            }
        }

        $schemaDefinition = (object)[];
        if (!isset($definition->parameters)) {
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
}
