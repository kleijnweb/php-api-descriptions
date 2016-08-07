<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Document;

use Doctrine\Common\Cache\Cache;
use KleijnWeb\ApiDescriptions\Description\Description;
use KleijnWeb\ApiDescriptions\Document\Definition\Loader\DefinitionLoader;
use KleijnWeb\ApiDescriptions\Document\Definition\RefResolver\RefResolver;
use KleijnWeb\ApiDescriptions\Document\Definition\Validator\DefinitionValidator;

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
     * @var DefinitionValidator
     */
    private $validator;

    /**
     * Repository constructor.
     *
     * @param string|null              $basePath
     * @param DefinitionValidator|null $validator
     * @param Cache|null               $cache
     * @param DefinitionLoader|null    $loader
     */
    public function __construct(
        string $basePath = null,
        DefinitionValidator $validator = null,
        Cache $cache = null,
        DefinitionLoader $loader = null
    ) {
        $this->basePath  = $basePath;
        $this->cache     = $cache;
        $this->loader    = $loader ?: new DefinitionLoader();
        $this->validator = $validator;
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

        $resolver = new RefResolver($this->loader->load($uri), $uri);
        $document = new Document($uri, $definition = $resolver->resolve());

        $specification = new Description($document);

        if ($this->validator) {
            $this->validator->validate($definition);
        }

        if ($this->cache) {
            $this->cache->save($uri, $specification);
        }

        return $specification;
    }
}
