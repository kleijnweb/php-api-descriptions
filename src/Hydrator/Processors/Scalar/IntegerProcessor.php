<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Exception\UnsupportedException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class IntegerProcessor extends ScalarProcessor
{
    /**
     * @var bool
     */
    protected $is32BitSystem;

    /**
     * IntegerHydrator constructor.
     * @param ScalarSchema $schema
     * @param bool         $force32Bit
     */
    public function __construct(ScalarSchema $schema, $force32Bit = false)
    {
        parent::__construct($schema);
        $this->is32BitSystem = $force32Bit === true ? true : PHP_INT_SIZE === 4;

        /** @var ScalarSchema $scalarSchema */
        $scalarSchema = $this->schema;

        if ($this->is32BitSystem && $scalarSchema->hasFormat(Schema::FORMAT_INT64)) {
            throw new UnsupportedException("Schema unsupported: Operating system does not support 64 bit integers");
        }
    }

    /**
     * @param $value
     * @return int
     */
    public function hydrate($value)
    {
        if ($this->is32BitSystem && $value > (float)PHP_INT_MAX) {
            throw new UnsupportedException("Value unsupported: Operating system does not support 64 bit integers");
        }

        return $value === null
            ? $this->schema->getDefault()
            : (int)$value;
    }
}
