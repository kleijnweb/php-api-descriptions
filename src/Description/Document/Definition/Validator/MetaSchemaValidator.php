<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\Validator;

use KleijnWeb\PhpApi\Descriptions\Description\Schema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator\SchemaValidator;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class MetaSchemaValidator
{
    /**
     * @var \stdClass
     */
    private $schema;

    /**
     * @var SchemaValidator
     */
    private $validator;

    /**
     * @var Schema\SchemaFactory
     */
    private $schemaFactory;

    /**
     * MetaSchemaValidator constructor.
     *
     * @param \stdClass       $schema
     * @param SchemaValidator $validator
     */
    public function __construct(\stdClass $schema, SchemaValidator $validator)
    {
        $this->schema        = $schema;
        $this->validator     = $validator;
        $this->schemaFactory = new Schema\SchemaFactory();
    }

    /**
     * @param \stdClass $definition
     */
    public function validate(\stdClass $definition)
    {
        if (!$this->validator->validate($this->schemaFactory->create($this->schema), $definition)->isValid()) {
            throw new \InvalidArgumentException("Description not valid");
        }
    }
}
