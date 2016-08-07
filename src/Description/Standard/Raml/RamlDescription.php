<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Description\Standard\Raml;

use KleijnWeb\ApiDescriptions\Description\Description;
use KleijnWeb\ApiDescriptions\Document\Document;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RamlDescription extends Description
{
    /**
     * Description constructor.
     *
     * @param Document $document
     */
    public function __construct(Document $document)
    {
        $this->document = $document;
        $this->schemes  = array_map('strtolower', isset($document->protocols) ? $document->protocols : []);

        $document = clone $document;

        $document->apply(function ($definition, $attributeName, $parent, $parentAttributeName) {
            if (substr((string)$attributeName, 0, 1) === '/') {
                $pathName               = "{$parentAttributeName}{$attributeName}";
                $this->paths[$pathName] = new RamlPath($pathName, $definition);
            }
        });
    }
}
