<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator;

use KleijnWeb\PhpApi\Descriptions\Hydrator\Exception\ClassNotFoundException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ClassNameResolver
{
    /**
     * @var array
     */
    protected $resourceNamespaces = [];

    /**
     * @var array
     */
    protected $cache = [];

    /**
     * ClassNameResolver constructor.
     *
     * @param array $resourceNamespaces
     */
    public function __construct(array $resourceNamespaces)
    {
        $this->resourceNamespaces = $resourceNamespaces;
    }

    /**
     * @param string $typeName
     *
     * @return string
     */
    public function resolve(string $typeName): string
    {
        if (!isset($this->cache[$typeName])) {
            foreach ($this->resourceNamespaces as $resourceNamespace) {
                if (class_exists($this->cache[$typeName] = $this->qualify($resourceNamespace, $typeName))) {
                    return $this->cache[$typeName];
                }
            }

            throw new ClassNotFoundException(
                sprintf(
                    "Did not find type '%s' in namespace(s) '%s'.",
                    $typeName,
                    implode(', ', $this->resourceNamespaces)
                )
            );
        }

        return $this->cache[$typeName];
    }

    /**
     * @param string $resourceNamespace
     * @param string $typeName
     *
     * @return string
     */
    protected function qualify(string $resourceNamespace, string $typeName): string
    {
        return ltrim("$resourceNamespace\\$typeName");
    }
}
