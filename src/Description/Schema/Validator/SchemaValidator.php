<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
interface SchemaValidator
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
    ): ValidationResult;
}
