<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class StrictSimpleObjectProcessor extends ObjectProcessor
{
    /**
     * @param \stdClass $input
     * @return \stdClass
     */
    public function hydrateObject(\stdClass $input)
    {
        $object = (object)[];

        /** @var ObjectSchema $objectSchema */
        $objectSchema = $this->schema;

        /**
         * @var string $name
         * @var Schema $propertySchema
         */
        foreach ($objectSchema->getPropertySchemas() as $name => $propertySchema) {
            if (!isset($input->$name) && isset($this->defaults[$name])) {
                $value = $this->defaults[$name];
            } else {
                $value = $input->$name;
            }
            $object->$name = $this->hydrateProperty($name, $value);
        }

        return $object;
    }

    /**
     * @param object $object
     * @return \stdClass
     */
    protected function dehydrateObject($object): \stdClass
    {
        $node = (object)[];
        /** @var ObjectSchema $objectSchema */
        $objectSchema = $this->schema;

        foreach ($object as $name => $value) {
            if ($this->shouldFilterOutputValue($objectSchema->getPropertySchema($name), $value)) {
                continue;
            } else {
                $node->$name = $this->dehydrateProperty($name, $value);
            }
        }

        return $node;
    }
}
