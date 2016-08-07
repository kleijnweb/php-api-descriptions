<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Description\OpenApi;

use KleijnWeb\ApiDescriptions\Description\Description;
use KleijnWeb\ApiDescriptions\Document\Document;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class OpenApiDescription extends Description
{
    /**
     * Description constructor.
     *
     * @param Document $document
     */
    public function __construct(Document $document)
    {
        $this->document = $document;
        $this->host     = isset($document->host) ? $document->host : null;
        $this->schemes  = isset($document->schemes) ? $document->schemes : [];

        if (isset($this->document->paths)) {
            foreach ($this->document->paths as $path => $pathItem) {
                $this->paths[$path] = new OpenApiPath($path, $pathItem);
            }
        }
    }
}
