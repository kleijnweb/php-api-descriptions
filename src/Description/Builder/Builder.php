<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Description\Builder;

use KleijnWeb\ApiDescriptions\Description\Description;
use KleijnWeb\ApiDescriptions\Description\Document\Document;
use KleijnWeb\ApiDescriptions\Description\Schema\SchemaFactory;

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
