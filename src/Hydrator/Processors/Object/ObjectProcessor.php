<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Processor;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
abstract class ObjectProcessor extends Processor
{
    /**
     * @var \KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Processor[]
     */
    protected $propertyProcessors;

    /**
     * @var array
     */
    protected $defaults = [];

    /**
     * ObjectHydrator constructor.
     * @param ObjectSchema $schema
     */
    public function __construct(ObjectSchema $schema)
    {
        parent::__construct($schema);

        /**
         * @var string $name
         * @var Schema $propertySchema
         */
        foreach ($schema->getPropertySchemas() as $name => $propertySchema) {
            if (!null !== $default = $propertySchema->getDefault()) {
                $this->defaults[$name] = $default;
            }
        }
    }

    /**
     * @param mixed $node
     * @return mixed
     */
    public function hydrate($node)
    {
        return $this->hydrateObject($node);
    }

    /**
     * @param mixed $node
     * @return mixed
     */
    public function dehydrate($node)
    {
        return $this->dehydrateObject($node);
    }

    /**
     * @param \KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Processor $child
     * @return ObjectProcessor
     */
    public function setPropertyProcessor(string $key, Processor $child): ObjectProcessor
    {
        $this->propertyProcessors[$key] = $child;

        return $this;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    protected function hydrateProperty($key, $data)
    {
        $next = $this->propertyProcessors[$key];

        return $next->hydrate($data);
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    protected function dehydrateProperty($key, $data)
    {
        $next = $this->propertyProcessors[$key];

        return $next->dehydrate($data);
    }

    /**
     * @param Schema $valueSchema
     * @param  mixed $value
     * @return bool
     */
    protected function shouldFilterOutputValue(Schema $valueSchema, $value): bool
    {
        return $value === null && (!$valueSchema instanceof ScalarSchema || !$valueSchema->isType(Schema::TYPE_NULL));
    }

    /**
     * @param \stdClass $input
     * @return object
     */
    abstract protected function hydrateObject(\stdClass $input);

    /**
     * @param object $input
     * @return \stdClass
     */
    abstract protected function dehydrateObject($input): \stdClass;
}
