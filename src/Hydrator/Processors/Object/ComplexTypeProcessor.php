<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
abstract class ComplexTypeProcessor extends ObjectProcessor
{
    /**
     * @var \ReflectionClass
     */
    protected $reflector;

    /**
     * @var \ReflectionProperty[]
     */
    protected $reflectionProperties = [];

    /**
     * @var string
     */
    protected $className;

    public function __construct(ObjectSchema $schema, string $className)
    {
        parent::__construct($schema);

        $this->reflector = new \ReflectionClass($this->className = $className);

        foreach ($this->reflector->getProperties() as $reflectionProperty) {
            $reflectionProperty->setAccessible(true);
            $this->reflectionProperties[$reflectionProperty->getName()] = $reflectionProperty;
        }
    }


    protected function dehydrateObject($object): \stdClass
    {
        $node = (object)[];

        /** @var ObjectSchema $objectSchema */
        $objectSchema = $this->schema;

        foreach ($objectSchema->getPropertySchemas() as $name => $propertySchema) {
            if (!isset($this->reflectionProperties[$name])) {
                if (!isset($this->defaults[$name])) {
                    continue;
                }
                $value = $this->defaults[$name];
            } else {
                $value = $this->reflectionProperties[$name]->getValue($object);
            }

            if ($this->shouldFilterOutputValue($objectSchema->getPropertySchema($name), $value)) {
                continue;
            }
            $node->$name = $this->dehydrateProperty($name, $value);
        }

        return $node;
    }
}
