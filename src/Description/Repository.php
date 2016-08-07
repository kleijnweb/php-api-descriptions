<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Description;

use Doctrine\Common\Cache\Cache;
use KleijnWeb\ApiDescriptions\Description\Factory\Factory;
use KleijnWeb\ApiDescriptions\Document\Definition\Loader\DefinitionLoader;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class Repository
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var array
     */
    private $specifications = [];

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var DefinitionLoader
     */
    private $loader;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * Repository constructor.
     *
     * @param string|null           $basePath
     * @param Cache|null            $cache
     * @param DefinitionLoader|null $loader
     * @param Factory|null          $factory
     */
    public function __construct(
        string $basePath = null,
        Cache $cache = null,
        DefinitionLoader $loader = null,
        Factory $factory = null
    ) {
        $this->basePath = $basePath;
        $this->cache    = $cache;
        $this->loader   = $loader ?: new DefinitionLoader();
        $this->factory  = $factory ?: new Factory();
    }

    /**
     * @param Factory $factory
     *
     * @return Repository
     */
    public function setFactory(Factory $factory): Repository
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * @param string $documentPath
     *
     * @return Description
     */
    public function get(string $documentPath): Description
    {
        if (!$documentPath) {
            throw new \InvalidArgumentException("No document path provided");
        }
        if ($this->basePath) {
            $documentPath = "$this->basePath/$documentPath";
        }
        if (!isset($this->specifications[$documentPath])) {
            $this->specifications[$documentPath] = $this->load($documentPath);
        }

        return $this->specifications[$documentPath];
    }

    /**
     * @param string $uri
     *
     * @return Description
     */
    private function load(string $uri): Description
    {
        if ($this->cache && $specification = $this->cache->fetch($uri)) {
            return $specification;
        }

        $specification = $this->factory->create($uri, $this->loader->load($uri));

        if ($this->cache) {
            $this->cache->save($uri, $specification);
        }

        return $specification;
    }
}
