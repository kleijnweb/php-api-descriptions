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
class NumberProcessor extends ScalarProcessor
{
    /**
     * NumberHydrator constructor.
     * @param ScalarSchema $schema
     */
    public function __construct(ScalarSchema $schema)
    {
        parent::__construct($schema);
    }

    /**
     * Cast a scalar value using the schema.
     *
     * @param mixed  $value
     * @param Schema $schema
     *
     * @return float|int|string|\DateTimeInterface
     * @throws UnsupportedException
     */
    public function hydrate($value)
    {
        if ($value === null) {
            return $this->schema->getDefault();
        }

        if (!is_string($value)) {
            return $value;
        }

        return !ctype_digit($value) ? (float)$value : (int)$value;
    }
}
