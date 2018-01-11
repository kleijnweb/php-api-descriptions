<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description;

use KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\Loader\DefinitionLoader;
use KleijnWeb\PhpApi\Descriptions\Description\Respository\RepositoryIterator;
use Psr\SimpleCache\CacheInterface;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class Repository implements \IteratorAggregate
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
     * @var array
     */
    private $uris = [];

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var DefinitionLoader
     */
    private $loader;

    /**
     * @var DescriptionFactory
     */
    private $factory;

    /**
     * Repository constructor.
     *
     * @param string|null             $basePath
     * @param CacheInterface|null     $cache
     * @param DefinitionLoader|null   $loader
     * @param DescriptionFactory|null $factory
     */
    public function __construct(
        string $basePath = null,
        CacheInterface $cache = null,
        DefinitionLoader $loader = null,
        DescriptionFactory $factory = null
    ) {
        $this->basePath = $basePath;
        $this->cache    = $cache;
        $this->loader   = $loader ?: new DefinitionLoader();
        $this->factory  = $factory ?: new DescriptionFactory();
    }

    /**
     * @param DescriptionFactory $factory
     *
     * @return Repository
     */
    public function setFactory(DescriptionFactory $factory): Repository
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * @param string $uri
     *
     * @return Description
     */
    public function get(string $uri): Description
    {
        if (!$uri) {
            throw new \InvalidArgumentException("No document URI provided");
        }
        if ($this->basePath) {
            $uri = "$this->basePath/$uri";
        }
        if (!isset($this->specifications[$uri])) {
            $this->specifications[$uri] = $this->load($uri);
        }

        return $this->specifications[$uri];
    }

    /**
     * @param string $uri
     *
     * @return Repository
     */
    public function register(string $uri): Repository
    {
        $this->uris[] = $uri;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getUris(): array
    {
        return $this->uris;
    }

    /**
     * @return  \Iterator
     */
    public function getIterator(): \Iterator
    {
        return new RepositoryIterator($this);
    }

    /**
     * @param string $uri
     *
     * @return Description
     */
    private function load(string $uri): Description
    {
        if ($this->cache && $description = $this->cache->get($cacheKey = bin2hex($uri))) {
            return $description;
        }

        $description = $this->factory->create($uri, $this->loader->load($uri));

        if (isset($cacheKey)) {
            $this->cache->set($cacheKey, $description);
        }

        return $description;
    }
}
