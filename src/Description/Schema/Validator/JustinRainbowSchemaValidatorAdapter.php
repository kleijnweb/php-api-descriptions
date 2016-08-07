<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Description\Schema\Validator;

use JsonSchema\Validator as JustinRainbowJsonSchemaValidator;
use KleijnWeb\ApiDescriptions\Description\Schema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class JustinRainbowSchemaValidatorAdapter implements SchemaValidator
{
    /**
     * @param Schema $schema
     * @param mixed  $value
     *
     * @return ValidationResult
     */
    public function validate(Schema $schema, $value): ValidationResult
    {
        $validator = new JustinRainbowJsonSchemaValidator();
        $validator->check($value, $schema->getDefinition());

        $map = [];
        foreach ($validator->getErrors() as $error) {
            $map[$error['property']] = $error['message'];
        }

        return new ValidationResult($validator->isValid(), $map);
    }
}
