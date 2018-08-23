<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
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
     *
     * @return object
     */
    protected function hydrateObject(\stdClass $input)
    {
        $object = $this->getObjectForHydration($input);
        $className = get_class($object);

        foreach ($this->reflectionProperties[$className] as $name => $reflectionProperty) {
            if ($this->hasReflectionProperty($className, $name)) {
                if (!property_exists($input, $name)) {
                    if (!isset($this->defaults[$name])) {
                        continue;
                    }
                    $value = $this->defaults[$name];
                } else {
                    $value = $input->$name;
                }

                if ($this->hasReflectionProperty($className, $name)) {
                    $value = $this->hydrateProperty($name, $value);
                }
                $this->getReflectionProperty($className, $name)->setValue($object, $value);
            }
        }

        return $object;
    }

    /**
     * @param \stdClass $input
     *
     * @return object
     */
    protected function getObjectForHydration(\stdClass $input)
    {
        $className = $this->defaultClassName;

        if (isset($input->{'x-type-name'})) {
            $type      = $this->types[$input->{'x-type-name'}];
            $className = $type->getClassName();
        }

        return unserialize(sprintf('O:%d:"%s":0:{}', strlen($className), $className));
    }
}
