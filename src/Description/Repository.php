<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\PhpApi\Descriptions\Description;

use Doctrine\Common\Cache\Cache;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\Loader\DefinitionLoader;

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
     * @var DescriptionFactory
     */
    private $factory;

    /**
     * Repository constructor.
     *
     * @param string|null             $basePath
     * @param Cache|null              $cache
     * @param DefinitionLoader|null   $loader
     * @param DescriptionFactory|null $factory
     */
    public function __construct(
        string $basePath = null,
        Cache $cache = null,
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
        if ($this->cache && $description = $this->cache->fetch($uri)) {
            return $description;
        }

        $description = $this->factory->create($uri, $this->loader->load($uri));

        if ($this->cache) {
            $this->cache->save($uri, $description);
        }

        return $description;
    }
}
