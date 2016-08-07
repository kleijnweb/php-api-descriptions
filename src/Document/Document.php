<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Document;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class Document implements \JsonSerializable, \IteratorAggregate
{
    /**
     * @var \stdClass
     */
    private $definition;

    /**
     * @var string
     */
    private $uri;

    /**
     * Document constructor.
     *
     * @param string    $uri
     * @param \stdClass $definition
     */
    public function __construct(string $uri, \stdClass $definition)
    {
        $this->definition = $definition;
        $this->uri        = $uri;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param callable $f
     *
     * @return void
     */
    public function apply(callable  $f)
    {
        $recurse = function (&$item, $parent = null, $parentAttribute = null) use ($f, &$recurse) {

            foreach ($item as $attribute => &$value) {
                if (false === $f($value, $attribute, $parent, $parentAttribute)) {
                    return false;
                }
                if ($value === null) {
                    return true;
                }
                if (!is_scalar($value)) {
                    if (false === $recurse($value, $item, $attribute)) {
                        return false;
                    }
                }
            }

            return true;
        };
        $recurse($this->definition);
    }

    /**
     * @return \stdClass
     */
    public function getDefinition(): \stdClass
    {
        return $this->definition;
    }

    /**
     * @param string $attribute
     *
     * @return mixed
     */
    public function __get(string $attribute)
    {
        return isset($this->definition->$attribute) ? $this->definition->$attribute : null;
    }

    /**
     * @param string $attribute
     *
     * @return bool
     */
    public function __isset(string $attribute)
    {
        return isset($this->definition->$attribute);
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize()
    {
        return $this->definition;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator((object)$this->definition);
    }
}
