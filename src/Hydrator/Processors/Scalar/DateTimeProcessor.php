<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\DateTimeSerializer;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class DateTimeProcessor extends ScalarProcessor
{
    /**
     * @var DateTimeSerializer
     */
    protected $dateTimeSerializer;


    public function __construct(ScalarSchema $schema, DateTimeSerializer $dateTimeSerializer)
    {
        parent::__construct($schema);
        $this->dateTimeSerializer = $dateTimeSerializer;
    }

    /**
     * @param string $value
     * @return \DateTime
     */
    public function hydrate($value)
    {
        $value = $value === null ? $this->schema->getDefault() : $value;

        return $this->dateTimeSerializer->deserialize($value, $this->schema);
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function dehydrate($value)
    {
        return $this->dateTimeSerializer->serialize($value, $this->schema);
    }
}
