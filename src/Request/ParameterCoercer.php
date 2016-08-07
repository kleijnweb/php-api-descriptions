<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Request;

use KleijnWeb\ApiDescriptions\Description\Parameter;
use KleijnWeb\ApiDescriptions\Description\Schema\Schema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ParameterCoercer
{
    /**
     * Try to coerce a value into its defined type.
     *
     * If coersion is not possible, will return the original value, to be picked up by validation.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param Parameter $parameter
     * @param mixed     $value
     *
     * @return mixed
     */
    public function coerce(Parameter $parameter, $value)
    {
        $schema = $parameter->getSchema();

        switch ($schema->getType()) {
            case Schema::TYPE_STRING:
                return (string)$value;
            case Schema::TYPE_BOOL:
                if (!is_scalar($value)) {
                    return $value;
                }
                $bool = $this->coerceBooleanValue($value);

                return $bool === null ? $value : $bool;
            case Schema::TYPE_NUMBER:
                if (!is_numeric($value)) {
                    return $value;
                }

                return ctype_digit($value) ? (int)$value : (float)$value;
            case Schema::TYPE_OBJECT:
                if (!is_array($value)) {
                    return $value == '' ? null : $value;
                }
                if (count($value) && is_numeric(key($value))) {
                    return $value;
                }

                return (object)$value;
            case Schema::TYPE_ARRAY:
                if (is_array($value) || !is_string($value)) {
                    return $value;
                }

                return $this->coerceArrayValue(
                    $parameter->getCollectionFormat() ? $parameter->getCollectionFormat() : 'csv',
                    $value
                );
            case Schema::TYPE_INT:
                if (!ctype_digit($value)) {
                    return $value;
                }

                return (integer)$value;
            case Schema::TYPE_NULL:
                if ($value !== '') {
                    return $value;
                }

                return null;
            default:
                return $value;
        }
    }

    /**
     * @param string $format
     * @param string $value
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function coerceArrayValue(string $format, $value): array
    {
        switch ($format) {
            case 'csv':
                return explode(',', $value);
            case 'ssv':
                return explode(' ', $value);
            case 'tsv':
                return explode("\t", $value);
            case 'pipes':
                return explode('|', $value);
            default:
                throw new \RuntimeException(
                    "Array 'collectionFormat' '$format' is not currently supported"
                );
        }
    }

    /**
     * @param $value
     *
     * @return bool|null
     */
    protected function coerceBooleanValue($value)
    {
        switch ((string)$value) {
            case 'TRUE':
            case 'true':
            case '1':
                return true;
            case 'FALSE':
            case 'false':
            case '0':
                return false;
            default:
                return null;
        }
    }
}
