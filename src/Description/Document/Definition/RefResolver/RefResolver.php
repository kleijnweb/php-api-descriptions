<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\RefResolver;

use KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\Loader\DefinitionLoader;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RefResolver
{
    /**
     * @var object
     */
    private $definition;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $directory;

    /**
     * @var DefinitionLoader
     */
    private $loader;

    /**
     * @param \stdClass        $definition
     * @param string           $uri
     * @param DefinitionLoader $loader
     */
    public function __construct(\stdClass $definition, $uri, DefinitionLoader $loader = null)
    {
        $this->definition = $definition;
        $this->uri        = $uri;
        $this->directory  = dirname($this->uri);
        $this->loader     = $loader ?: new DefinitionLoader();
    }

    /**
     * @return \stdClass
     */
    public function getDefinition(): \stdClass
    {
        return $this->definition;
    }

    /**
     * Resolve all references
     *
     * @return mixed The whole definition can be a reference to a scalar value
     */
    public function resolve()
    {
        $this->resolveRecursively($this->definition);

        return $this->definition;
    }

    /**
     * Revert to original state
     *
     * @return \stdClass
     */
    public function unresolve(): \stdClass
    {
        $this->unresolveRecursively($this->definition);

        return $this->definition;
    }

    /**
     * @param object|array $current
     * @param \stdClass    $document
     * @param string       $uri
     * @throws InvalidReferenceException
     */
    private function resolveRecursively(&$current, \stdClass $document = null, string $uri = null)
    {
        $document = $document ?: $this->definition;
        $uri      = $uri ?: $this->uri;

        if (is_array($current)) {
            foreach ($current as &$value) {
                $this->resolveRecursively($value, $document, $uri);
            }
        } elseif (is_object($current)) {
            if (property_exists($current, '$ref')) {
                $uri = $current->{'$ref'};
                if ('#' === $uri[0]) {
                    $current = $this->lookup($uri, $document);
                    $this->resolveRecursively($current, $document, $uri);
                } else {
                    $uriSegs          = $this->parseUri($uri);
                    $normalizedUri    = $this->normalizeFileUri($uriSegs);
                    $externalDocument = $this->loadExternal($normalizedUri);
                    $current          = $this->lookup($uriSegs['fragment'], $externalDocument, $normalizedUri);
                    $this->resolveRecursively($current, $externalDocument, $normalizedUri);
                }
                if (is_object($current)) {
                    $current->{'x-ref-id'} = $uri;
                }

                return;
            }
            foreach ($current as $propertyName => &$propertyValue) {
                $this->resolveRecursively($propertyValue, $document, $uri);
            }
        }
    }

    /**
     * @param object|array $current
     * @param object|array $parent
     *
     * @return void
     */
    private function unresolveRecursively(&$current, &$parent = null)
    {
        foreach ($current as $key => &$value) {
            if ($value !== null && !is_scalar($value)) {
                $this->unresolveRecursively($value, $current);
            }
            if ($key === 'x-ref-id') {
                $parent = (object)['$ref' => $value];
            }
        }
    }

    /**
     * @param string    $path
     * @param \stdClass $document
     * @param string    $uri
     *
     * @return mixed
     * @throws InvalidReferenceException
     */
    private function lookup($path, \stdClass $document, string $uri = null)
    {
        $target = $this->lookupRecursively(
            explode('/', trim($path, '/#')),
            $document
        );
        if (!$target) {
            throw new InvalidReferenceException("Target '$path' does not exist'" . ($uri ? " at '$uri''" : ''));
        }

        return $target;
    }

    /**
     * @param array     $segments
     * @param \stdClass $context
     *
     * @return mixed
     */
    private function lookupRecursively(array $segments, \stdClass $context)
    {
        $segment = str_replace(['~0', '~1'], ['~', '/'], array_shift($segments));
        if (property_exists($context, $segment)) {
            if (!count($segments)) {
                return $context->$segment;
            }

            return $this->lookupRecursively($segments, $context->$segment);
        }

        return null;
    }

    /**
     * @param string $fileUrl
     *
     * @return \stdClass
     */
    private function loadExternal(string $fileUrl): \stdClass
    {
        return $this->loader->load($fileUrl);
    }

    /**
     * @param array $uriSegments
     *
     * @return string
     */
    private function normalizeFileUri(array $uriSegments): string
    {
        $path  = $uriSegments['path'];
        $auth  = !$uriSegments['user'] ? '' : "{$uriSegments['user']}:{$uriSegments['pass']}@";
        $query = !$uriSegments['query'] ? '' : "?{$uriSegments['query']}";
        $port  = !$uriSegments['port'] ? '' : ":{$uriSegments['port']}";
        $host  = !$uriSegments['host'] ? '' : "{$uriSegments['scheme']}://$auth{$uriSegments['host']}{$port}";

        if (substr($path, 0, 1) !== '/') {
            $path = "$this->directory/$path";
        }

        return "{$host}{$path}{$query}";
    }

    /**
     * @param string $uri
     *
     * @return array
     */
    private function parseUri(string $uri): array
    {
        $defaults = [
            'scheme'   => '',
            'host'     => '',
            'port'     => '',
            'user'     => '',
            'pass'     => '',
            'path'     => '',
            'query'    => '',
            'fragment' => '',
        ];

        if (0 === strpos($uri, 'file://')) {
            // parse_url botches this up
            preg_match('@file://(?P<path>[^#]*)(?P<fragment>#.*)?@', $uri, $matches);

            return array_merge($defaults, array_intersect_key($matches, $defaults));
        }

        return array_merge($defaults, array_intersect_key(parse_url($uri), $defaults));
    }
}
