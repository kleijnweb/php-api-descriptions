<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\AnyProcessor;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class LooseSimpleObjectProcessor extends ObjectProcessor
{
    /**
     * @var AnyProcessor
     */
    protected $anyProcessor;

    /**
     * LooseSimpleObjectHydrator constructor.
     * @param ObjectSchema $schema
     * @param AnyProcessor $anyProcessor
     */
    public function __construct(ObjectSchema $schema, AnyProcessor $anyProcessor)
    {
        parent::__construct($schema);
        $this->anyProcessor = $anyProcessor;
    }

    /**
     * @param \stdClass $input
     * @return \stdClass
     */
    public function hydrateObject(\stdClass $input)
    {
        $output = (object)[];

        /** @var ObjectSchema $objectSchema */
        $objectSchema = $this->schema;

        /** @var Schema $propertySchema */
        foreach ($objectSchema->getPropertySchemas() as $name => $propertySchema) {
            if (!property_exists($input, $name)) {
                if (!isset($this->defaults[$name])) {
                    continue;
                }
                $value = $this->defaults[$name];
            } else {
                $value = $input->$name;
            }
            $output->$name = $this->hydrateProperty($name, $value);
        }

        foreach ($input as $name => $value) {
            if (!isset($output->$name)) {
                $output->$name = $this->anyProcessor->hydrate($value);
            }
        }

        return $output;
    }

    /**
     * @param object $input
     * @return \stdClass
     */
    protected function dehydrateObject($input): \stdClass
    {
        /** @var ObjectSchema $objectSchema */
        $objectSchema = $this->schema;

        /** @var Schema[] $propertySchemas */
        $propertySchemas = [];

        $output = clone $input;

        foreach ($input as $name => $value) {
            if ($objectSchema->hasPropertySchema($name)) {
                $propertySchemas[$name] = $objectSchema->getPropertySchema($name);
            } else {
                $output->$name = $this->anyProcessor->dehydrate($value);
            }
        }

        foreach ($propertySchemas as $name => $propertySchema) {
            if ($this->shouldFilterOutputValue($propertySchema, $input->$name)) {
                continue;
            } else {
                $output->$name = $this->dehydrateProperty($name, $input->$name);
            }
        }

        return $output;
    }
}
