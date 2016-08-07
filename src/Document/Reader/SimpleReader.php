<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Document\Reader;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SimpleReader implements Reader
{
    const CONTENT_TYPE_YAML = 'application/yml';
    const CONTENT_TYPE_JSON = 'application/json';

    /**
     * @param string $uri
     *
     * @return Response
     */
    public function read(string $uri): Response
    {
        $contents = @file_get_contents($uri);

        if (false === $contents) {
            throw new ResourceNotReadableException("Failed reading '$uri'");
        }

        return new Response(
            preg_match('/\b(yml|yaml|raml)\b/', $uri) ? self::CONTENT_TYPE_YAML : self::CONTENT_TYPE_JSON,
            $contents
        );
    }
}
