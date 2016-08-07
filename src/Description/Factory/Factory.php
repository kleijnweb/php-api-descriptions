<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Description\Factory;

use KleijnWeb\ApiDescriptions\Description\Description;
use KleijnWeb\ApiDescriptions\Description\Standard\OpenApi\OpenApiFactory;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class Factory
{
    /**
     * @var StandardFactory
     */
    private $standardFactory;

    /**
     * Factory constructor.
     *
     * @param StandardFactory $standardFactory
     */
    public function __construct(StandardFactory $standardFactory = null)
    {
        $this->standardFactory = $standardFactory ?: new OpenApiFactory();
    }

    /**
     * @param string    $uri
     * @param \stdClass $definition
     *
     * @return Description
     */
    public function create(string $uri, \stdClass $definition): Description
    {
        return $this->standardFactory->build($uri, $definition);
    }
}
