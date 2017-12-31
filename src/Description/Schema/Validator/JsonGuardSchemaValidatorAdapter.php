<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator;

use League\JsonGuard\Validator as JsonGuardSchemaValidator;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class JsonGuardSchemaValidatorAdapter extends SchemaValidatorAdapter
{
    /**
     * @param \stdClass $definition
     * @param  mixed    $value
     * @return ValidationResult
     */
    protected function getResult(\stdClass $definition, $value): ValidationResult
    {
        $validator = new JsonGuardSchemaValidator($value, $definition);

        $map = [];
        foreach ($validator->errors() as $error) {
            $map[$error->getDataPath()] = $error->getMessage();
        }

        return new ValidationResult(!$validator->fails(), $map);
    }
}
