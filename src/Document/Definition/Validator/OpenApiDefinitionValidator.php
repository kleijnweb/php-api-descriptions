<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Document\Definition\Validator;

use KleijnWeb\ApiDescriptions\Description\Schema\Validator\DefaultValidator;
use KleijnWeb\ApiDescriptions\Description\Schema\Validator\SchemaValidator;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class OpenApiDefinitionValidator extends MetaSchemaValidator
{
    /**
     * SpecificationValidator constructor.
     *
     * @param SchemaValidator  $validator
     * @param string|\stdClass $schema
     */
    public function __construct(SchemaValidator $validator = null, $schema = null)
    {
        if (null === $schema) {
            $schema = json_decode(file_get_contents(__DIR__ . '/../../../../assets/swagger-schema.json'));
        }
        parent::__construct($schema, $validator ?: new DefaultValidator()) ;
    }
}
