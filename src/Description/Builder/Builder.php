<?php declare(strict_types = 1);
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
        'head'
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
     * OpenApiBuilder constructor.
     *
     * @param Document $document
     */
    public function __construct(Document $document)
    {
        $this->document      = $document;
        $this->schemaFactory = new SchemaFactory();
    }

    abstract public function build(): Description;
}
