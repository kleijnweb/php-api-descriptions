<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Object;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ComplexTypePropertyProcessor extends ComplexTypeProcessor
{
    /**
     * @param \stdClass $input
     * @return object
     */
    protected function hydrateObject(\stdClass $input)
    {
        $object = $this->getObjectForHydration($input);

        foreach ($this->reflectionProperties as $name => $reflectionProperty) {
            if (isset($this->reflectionProperties[$name])) {
                if (!property_exists($input, $name)) {
                    if (!isset($this->defaults[$name])) {
                        continue;
                    }
                    $value = $this->defaults[$name];
                } else {
                    $value = $input->$name;
                }

                if (isset($this->propertyProcessors[$name])) {
                    $value = $this->hydrateProperty($name, $value);
                }
                $this->reflectionProperties[$name]->setValue($object, $value);
            }
        }

        return $object;
    }

    /**
     * @param \stdClass $input
     * @return object
     */
    protected function getObjectForHydration(\stdClass $input)
    {
        return unserialize(sprintf('O:%d:"%s":0:{}', strlen($this->className), $this->className));
    }
}
