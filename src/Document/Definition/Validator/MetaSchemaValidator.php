<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Document\Definition\Validator;

use KleijnWeb\ApiDescriptions\Description\Schema;
use KleijnWeb\ApiDescriptions\Description\Schema\Validator\SchemaValidator;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class MetaSchemaValidator
{
    /**
     * @var string
     */
    private $schema;

    /**
     * @var SchemaValidator
     */
    private $validator;

    /**
     * MetaSchemaValidator constructor.
     *
     * @param \stdClass       $schema
     * @param SchemaValidator $validator
     */
    public function __construct(\stdClass $schema, SchemaValidator $validator)
    {
        $this->schema    = $schema;
        $this->validator = $validator;
    }

    /**
     * @param \stdClass $definition
     */
    public function validate(\stdClass $definition)
    {
        if (!$this->validator->validate(Schema::get($this->schema), $definition)->isValid()) {
            throw new \InvalidArgumentException("Description not valid");
        }
    }
}
