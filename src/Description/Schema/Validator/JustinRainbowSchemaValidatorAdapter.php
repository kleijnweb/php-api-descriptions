<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator;

use JsonSchema\Validator as JustinRainbowJsonSchemaValidator;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class JustinRainbowSchemaValidatorAdapter implements SchemaValidator
{
    /**
     * @param Schema $schema
     * @param mixed  $value
     * @param bool   $forceNoAdditionalProperties
     * @param bool   $requireAllWhenNotSpecified
     *
     * @return ValidationResult
     */
    public function validate(
        Schema $schema,
        $value,
        $forceNoAdditionalProperties = false,
        $requireAllWhenNotSpecified = false
    ): ValidationResult {

        $definition = $schema->getDefinition();

        if ($requireAllWhenNotSpecified || $forceNoAdditionalProperties) {
            $hackDefinition = function (\stdClass $definition) use (
                $forceNoAdditionalProperties,
                $requireAllWhenNotSpecified,
                &$hackDefinition
            ) {
                if (isset($definition->properties)) {
                    if ($forceNoAdditionalProperties) {
                        $definition->additionalProperties = false;
                    }
                    if ($requireAllWhenNotSpecified && !isset($definition->required)) {
                        $definition->required = array_keys((array)$definition->properties);
                    }
                }

                foreach ($definition as $item) {
                    if ($item instanceof \stdClass) {
                        $hackDefinition($item);
                    }
                }
            };
            $hackDefinition($definition = clone $definition);
        }

        $validator = new JustinRainbowJsonSchemaValidator();
        $validator->check($value, $definition);

        $map = [];
        foreach ($validator->getErrors() as $error) {
            $map[$error['property']] = $error['message'];
        }

        return new ValidationResult($validator->isValid(), $map);
    }
}
