<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description;

use KleijnWeb\PhpApi\Descriptions\Description\Document\Document;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Description\Visitor\ClosureVisitorScope;
use KleijnWeb\PhpApi\Descriptions\Description\Visitor\Visitee;
use KleijnWeb\PhpApi\Descriptions\Description\Visitor\VisiteeMixin;

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
     * @var array
     */
    protected $extensions = [];

    /**
     * @var Document
     */
    protected $document;

    /**
     * @var string
     */
    private $basePath;

    /**
     * Description constructor.
     *
     * @param Path[]        $paths
     * @param ComplexType[] $complexTypes
     * @param string        $host
     * @param array         $schemes
     * @param array         $extensions
     * @param Document      $document
     * @param string        $basePath
     */
    public function __construct(
        array $paths,
        array $complexTypes,
        $host,
        array $schemes,
        array $extensions,
        Document $document,
        string $basePath = ''
    ) {
        $this->paths        = $paths;
        $this->complexTypes = $complexTypes;
        $this->host         = $host;
        $this->schemes      = $schemes;
        $this->document     = $document;
        $this->extensions   = $extensions;
        $this->basePath     = $basePath;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getExtension(string $name)
    {
        return isset($this->extensions[$name]) ? $this->extensions[$name] : null;
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
        if (!$this->hasPath($path)) {
            throw new \InvalidArgumentException(
                "Path '$path' does not exist (have " . implode(', ', array_keys($this->paths)) . ')'
            );
        }

        return $this->paths[$path];
    }

    /**
     * @param string $path
     * @return bool
     */
    public function hasPath(string $path): bool
    {
        return isset($this->paths[$path]);
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
     * @return Parameter|null
     */
    public function getRequestBodyParameter(string $path, string $method)
    {
        foreach ($this->getPath($path)->getOperation($method)->getParameters() as $parameter) {
            if ($parameter->getIn() === Parameter::IN_BODY) {
                return $parameter;
            }
        }

        return null;
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
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @return Document
     */
    public function getDocument(): Document
    {
        return $this->document;
    }
}
