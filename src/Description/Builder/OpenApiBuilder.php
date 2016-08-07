<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Description\Builder;

use KleijnWeb\ApiDescriptions\Description\ComplexType;
use KleijnWeb\ApiDescriptions\Description\Description;
use KleijnWeb\ApiDescriptions\Description\Operation;
use KleijnWeb\ApiDescriptions\Description\Parameter;
use KleijnWeb\ApiDescriptions\Description\Path;
use KleijnWeb\ApiDescriptions\Description\Response;
use KleijnWeb\ApiDescriptions\Description\Schema\ObjectSchema;
use KleijnWeb\ApiDescriptions\Description\Schema\Schema;
use KleijnWeb\ApiDescriptions\Description\Visitor\ClosureVisitor;
use KleijnWeb\ApiDescriptions\Description\Visitor\ClosureVisitorScope;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class OpenApiBuilder extends Builder implements ClosureVisitorScope
{
    /**
     * @return Description
     */
    public function build(): Description
    {
        $host    = isset($this->document->host) ? $this->document->host : null;
        $schemes = isset($this->document->schemes) ? $this->document->schemes : [];
        $paths   = [];
        if (isset($this->document->paths)) {
            foreach ($this->document->paths as $path => $pathItem) {
                $paths[$path] = $this->createPath($path, $pathItem);
            }
        }

        $description = new Description($paths, [], $host, $schemes, $this->document);

        /** @var ObjectSchema[] $typeDefinitions */
        $typeDefinitions = [];

        $description->accept(new ClosureVisitor($this, function ($schema) use (&$typeDefinitions) {
            if ($schema instanceof ObjectSchema) {
                if ($schema->isType(Schema::TYPE_OBJECT) && isset($schema->getDefinition()->{'x-ref-id'})) {
                    $typeName = substr(
                        $schema->getDefinition()->{'x-ref-id'},
                        strrpos($schema->getDefinition()->{'x-ref-id'}, '/') + 1
                    );

                    $typeDefinitions[$typeName] = $schema;
                }
            }
        }));

        foreach ($typeDefinitions as $name => $schema) {
            $type = new ComplexType($name, $schema);
            $schema->setComplexType($type);
            $complexTypes[] = $type;
        }

        $description->accept(new ClosureVisitor($description, function () use (&$complexTypes) {
            $this->complexTypes = $complexTypes;
        }));

        return $description;
    }

    /**
     * @param string    $pathName
     * @param \stdClass $definition
     *
     * @return Path
     */
    protected function createPath(string $pathName, \stdClass $definition)
    {
        $pathParameters = $this->extractParameters($definition);

        /** @var Operation[] $operations */
        $operations = [];
        foreach (self::$methodNames as $method) {
            if (isset($definition->$method)) {
                $operations[$method] = $this->createOperation(
                    $definition->$method,
                    $pathName,
                    $method,
                    $pathParameters
                );
            }
        }

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
        $parameters = array_merge($pathParameters, self::extractParameters($definition));
        $responses  = [];

        if (isset($definition->responses)) {
            $hasOkResponse = false;
            foreach ($definition->responses as $code => $responseDefinition) {
                $code = (string)$code;
                if ($code === 'default' || substr((string)$code, 1) === '2') {
                    $hasOkResponse = true;
                }
                $code             = (int)$code;
                $responses[$code] = $this->createResponse($code, $responseDefinition);
            }
            if (!$hasOkResponse) {
                $responses[200] = $this->createResponse(200, (object)[]);
            }
        }

        $schemaDefinition = (object)[];
        if (!isset($definition->parameters)) {
            $schemaDefinition->type = 'null';
            $requestSchema          = $this->schemaFactory->create($schemaDefinition);
        } else {
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
        }

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

        if (isset($definition->parameters)) {
            foreach ($definition->parameters as $parameterDefinition) {
                $parameters[] = $this->createParameter($parameterDefinition);
            }
        }

        return $parameters;
    }

    /**
     * @param \stdClass $definition
     *
     * @return Parameter
     */
    protected function createParameter(\stdClass $definition)
    {
        if ($definition->in === Parameter::IN_BODY) {
            $definition->schema       = isset($definition->schema) ? $definition->schema : (object)[];
            $definition->schema->type = 'object';
        }
        if (isset($definition->schema)) {
            $schema = $this->schemaFactory->create($definition->schema);
        } else {
            $schema = $this->createParameterSchema($definition);
        }

        $required         = isset($definition->required) && $definition->required;
        $collectionFormat = isset($definition->collectionFormat) ? $definition->collectionFormat : null;

        return new Parameter($definition->name, $required, $schema, $definition->in, $collectionFormat);
    }

    /**
     * @param int       $code
     * @param \stdClass $definition
     *
     * @return Response
     */
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
