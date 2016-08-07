<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Description\Builder;

use KleijnWeb\ApiDescriptions\Description\Description;
use KleijnWeb\ApiDescriptions\Description\Operation;
use KleijnWeb\ApiDescriptions\Description\Parameter;
use KleijnWeb\ApiDescriptions\Description\Path;
use KleijnWeb\ApiDescriptions\Description\Response;
use KleijnWeb\ApiDescriptions\Description\Schema\Schema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RamlBuilder extends Builder
{

    /**
     * @return Description
     */
    public function build(): Description
    {
        $schemes = array_map('strtolower', isset($this->document->protocols) ? $this->document->protocols : []);
        $paths   = [];

        $this->document->apply(function ($definition, $attributeName, $parent, $parentAttributeName) use (&$paths) {
            if (substr((string)$attributeName, 0, 1) === '/') {
                $pathName         = "{$parentAttributeName}{$attributeName}";
                $paths[$pathName] = $this->createPath($pathName, $definition);
            }
        });

        return new Description($paths, [], '', $schemes, $this->document);
    }

    /**
     * @param string      $pathName
     * @param \stdClass   $definition
     *
     * @param Parameter[] $pathParameters
     *
     * @return Path
     */
    protected function createPath(string $pathName, \stdClass $definition, array $pathParameters = [])
    {
        /** @var Operation[] $operations */
        $operations = [];
        foreach (self::$methodNames as $method) {
            if (isset($definition->$method)) {
                $operations[$method] = $this->createOperation($definition->$method, $pathName, $method);
            }
        }
        $pathParameters = array_merge($pathParameters, $this->extractParameters($definition));

        return new Path($pathName, $operations, $pathParameters);
    }

    /**
     * @param \stdClass $definition
     * @param string    $path
     * @param string    $method
     * @param array     $pathParameters
     *
     * @return Operation
     */
    protected function createOperation(
        \stdClass $definition,
        string $path,
        string $method,
        array $pathParameters = []
    ): Operation {
    


        /** @var Parameter[] $parameters */
        $parameters = array_merge($pathParameters, $this->extractParameters($definition));
        $responses  = [];

        if (isset($definition->responses)) {
            $hasOkResponse = false;
            foreach ($definition->responses as $code => $responseDefinition) {
                $code             = (int)$code;
                $responses[$code] = $this->createResponse($code, $responseDefinition);
            }
            if (!$hasOkResponse) {
                $responses[200] = $this->createResponse(200, (object)[]);
            }
        }

        $schemaDefinition             = (object)[];
        $schemaDefinition->type       = 'object';
        $schemaDefinition->required   = [];
        $schemaDefinition->properties = (object)[];

        foreach ($parameters as $parameter) {
            if ($parameter->isRequired()) {
                $schemaDefinition->required[] = $parameter->getName();
            }
            $schemaDefinition->properties->{$parameter->getName()} = $parameter->getSchema()->getDefinition();
        }

        $requestSchema = $this->schemaFactory->create($schemaDefinition);

        return new Operation($path, $method, $parameters, $requestSchema, $responses);
    }

    /**
     * @param \stdClass $definition
     *
     * @return array
     */
    protected function extractParameters(\stdClass $definition)
    {
        $parameters = [];

        if (isset($definition->queryParameters)) {
            foreach ($definition->queryParameters as $name => $parameterDefinition) {
                $parameters[] = $this->createParameter($name, Parameter::IN_QUERY, $parameterDefinition);
            }
        }
        if (isset($definition->uriParameters)) {
            foreach ($definition->uriParameters as $name => $parameterDefinition) {
                $parameters[] = $this->createParameter($name, Parameter::IN_PATH, $parameterDefinition);
            }
        }

        return $parameters;
    }

    /**
     * @param string    $name
     * @param string    $in
     * @param \stdClass $definition
     *
     * @return Parameter
     */
    protected function createParameter(string $name, string $in, \stdClass $definition)
    {
        $schema   = $this->createParameterSchema($definition);
        $required = isset($definition->required) && $definition->required;

        return new Parameter($name, $required, $schema, $in);
    }

    protected function createResponse(int $code, \stdClass $definition)
    {
        return new Response(
            $code,
            $this->schemaFactory->create(isset($definition->schema) ? $definition->schema : null)
        );
    }

    /**
     * @param \stdClass $definition
     *
     * @return Schema
     */
    protected function createParameterSchema(\stdClass $definition): Schema
    {
        // Remove non-JSON-Schema properties
        $schemaDefinition     = clone $definition;
        $swaggerPropertyNames = [
            'name',
            'in',
            'description',
            'required',
            'allowEmptyValue',
            'collectionFormat'
        ];
        foreach ($swaggerPropertyNames as $propertyName) {
            if (property_exists($schemaDefinition, $propertyName)) {
                unset($schemaDefinition->$propertyName);
            }
        }

        return $this->schemaFactory->create($schemaDefinition);
    }
}
