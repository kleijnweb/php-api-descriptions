<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description\Builder;

use KleijnWeb\PhpApi\Descriptions\Description\Description;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Document;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\SchemaFactory;
use KleijnWeb\PhpApi\Descriptions\Hydrator\ClassNameResolver;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
abstract class Builder
{
    protected static $methodNames = [
        'get',
        'patch',
        'put',
        'post',
        'delete',
        'options',
        'head',
    ];

    /**
     * @var \stdClass
     */
    protected $document;

    /**
     * @var SchemaFactory
     */
    protected $schemaFactory;

    /**
     * @var ClassNameResolver
     */
    protected $classNameResolver;

    public function __construct(Document $document, ClassNameResolver $classNameResolver = null)
    {
        $this->document          = $document;
        $this->schemaFactory     = new SchemaFactory();
        $this->classNameResolver = $classNameResolver;
    }

    abstract public function build(): Description;
}
