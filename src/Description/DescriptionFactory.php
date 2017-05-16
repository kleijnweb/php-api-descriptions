<?php declare(strict_types = 1);
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
     * DescriptionFactory constructor.
     *
     * @param string $type
     */
    public function __construct(string $type = self::BUILDER_OPEN_API)
    {
        $this->type = $type;
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
                    )
                )
                )->build();
            case self::BUILDER_RAML:
                return (
                new RamlBuilder(
                    new Document(
                        $uri,
                        $definition
                    )
                )
                )->build();
            default:
                throw new \InvalidArgumentException();
        }
    }
}
