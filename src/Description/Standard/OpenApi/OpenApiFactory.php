<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Description\Standard\OpenApi;

use KleijnWeb\ApiDescriptions\Description\Description;
use KleijnWeb\ApiDescriptions\Description\Factory\StandardFactory;
use KleijnWeb\ApiDescriptions\Document\Definition\RefResolver\RefResolver;
use KleijnWeb\ApiDescriptions\Document\Definition\Validator\DefinitionValidator;
use KleijnWeb\ApiDescriptions\Document\Document;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class OpenApiFactory implements StandardFactory
{
    /**
     * @var DefinitionValidator|null
     */
    private $validator;

    /**
     * Repository constructor.
     *
     * @param DefinitionValidator|null $validator
     */
    public function __construct(DefinitionValidator $validator = null)
    {
        $this->validator = $validator;
    }

    /**
     * @param string    $uri
     * @param \stdClass $definition
     *
     * @return Description
     */
    public function build(string $uri, \stdClass $definition): Description
    {
        $resolver = new RefResolver($definition, $uri);

        /** @var \stdClass $definition */
        $description = new OpenApiDescription(new Document($uri, $definition = $resolver->resolve()));

        if ($this->validator) {
            $this->validator->validate($definition);
        }

        return $description;
    }
}
