<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description;

use KleijnWeb\PhpApi\Descriptions\Description\Builder\OpenApiBuilder;
use KleijnWeb\PhpApi\Descriptions\Description\Builder\RamlBuilder;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\RefResolver\RefResolver;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Document;
use KleijnWeb\PhpApi\Descriptions\Hydrator\ClassNameResolver;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class DescriptionFactory
{
    const BUILDER_OPEN_API = 'openapi';
    const BUILDER_RAML     = 'raml';

    /**
     * @var string
     */
    private $type;

    /**
     * @var ClassNameResolver
     */
    private $classNameResolver;

    /**
     * DescriptionFactory constructor.
     *
     * @param string                 $type
     * @param ClassNameResolver|null $classNameResolver
     */
    public function __construct(string $type = self::BUILDER_OPEN_API, ClassNameResolver $classNameResolver = null)
    {
        $this->type              = $type;
        $this->classNameResolver = $classNameResolver;
    }

    /**
     * @param string    $uri
     * @param \stdClass $definition
     *
     * @return Description
     */
    public function create(string $uri, \stdClass $definition): Description
    {
        switch ($this->type) {
            case self::BUILDER_OPEN_API:
                return (
                new OpenApiBuilder(
                    new Document(
                        $uri,
                        (new RefResolver($definition, $uri))->resolve()
                    ),
                    $this->classNameResolver
                )
                )->build();
            case self::BUILDER_RAML:
                return (
                new RamlBuilder(
                    new Document(
                        $uri,
                        $definition
                    ),
                    $this->classNameResolver
                )
                )->build();
            default:
                throw new \InvalidArgumentException();
        }
    }
}
