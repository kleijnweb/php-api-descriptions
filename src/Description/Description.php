<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Description;

use KleijnWeb\ApiDescriptions\Description\Document\Document;
use KleijnWeb\ApiDescriptions\Description\Schema\Schema;
use KleijnWeb\ApiDescriptions\Description\Visitor\ClosureVisitorScope;
use KleijnWeb\ApiDescriptions\Description\Visitor\Visitee;
use KleijnWeb\ApiDescriptions\Description\Visitor\VisiteeMixin;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class Description implements Visitee, ClosureVisitorScope
{
    use VisiteeMixin;

    /**
     * @var Path[]
     */
    protected $paths;

    /**
     * @var ComplexType[]
     */
    protected $complexTypes;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var array
     */
    protected $schemes = [];

    /**
     * @var Document
     */
    protected $document;

    /**
     * Description constructor.
     *
     * @param Path[]        $paths
     * @param ComplexType[] $complexTypes
     * @param string        $host
     * @param array         $schemes
     * @param Document      $document
     */
    public function __construct(array $paths, array $complexTypes, $host, array $schemes, Document $document)
    {
        $this->paths        = $paths;
        $this->complexTypes = $complexTypes;
        $this->host         = $host;
        $this->schemes      = $schemes;
        $this->document     = $document;
    }

    /**
     * @return ComplexType[]
     */
    public function getComplexTypes(): array
    {
        return $this->complexTypes;
    }

    /**
     * @return array
     */
    public function getSchemes(): array
    {
        return $this->schemes;
    }

    /**
     * @return string|null
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $path
     *
     * @return Path
     */
    public function getPath(string $path): Path
    {
        if (!isset($this->paths[$path])) {
            throw new \InvalidArgumentException(
                "Path '$path' does not exist (have " . implode(', ', array_keys($this->paths)) . ')'
            );
        }

        return $this->paths[$path];
    }

    /**
     * @param string $path
     * @param string $method
     *
     * @return Schema
     */
    public function getRequestSchema(string $path, string $method): Schema
    {
        return $this->getPath($path)->getOperation($method)->getRequestSchema();
    }

    /**
     * @param string $path
     * @param string $method
     *
     * @param int    $code
     *
     * @return Schema
     */
    public function getResponseSchema(string $path, string $method, int $code): Schema
    {
        return $this->getPath($path)->getOperation($method)->getResponse($code)->getSchema();
    }

    /**
     * @return Path[]
     */
    public function getPaths(): array
    {
        return array_values($this->paths);
    }

    /**
     * @return Document
     */
    public function getDocument(): Document
    {
        return $this->document;
    }
}
