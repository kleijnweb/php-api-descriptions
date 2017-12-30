<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\AnySchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\DateTimeSerializer;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Exception\UnsupportedException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class AnyProcessor extends Processor
{
    /**
     * @var DateTimeSerializer
     */
    protected $dateTimeSerializer;

    /**
     * @var ScalarSchema
     */
    protected $dateSchema;


    /**
     * AnyProcessor constructor.
     * @param AnySchema          $schema
     * @param DateTimeSerializer $dateTimeSerializer
     */
    public function __construct(AnySchema $schema, DateTimeSerializer $dateTimeSerializer)
    {
        parent::__construct($schema);

        $this->dateTimeSerializer = $dateTimeSerializer;

        $this->dateSchema = new ScalarSchema(
            (object)['type' => Schema::TYPE_STRING, 'format' => Schema::FORMAT_DATE]
        );
    }

    /**
     * Cast a scalar value using the schema.
     *
     * @param mixed $value
     *
     * @return float|int|string|\DateTimeInterface
     * @throws UnsupportedException
     */
    public function hydrate($value)
    {
        if ($value instanceof \stdClass) {
            $value = clone $value;
            foreach ($value as $name => $propertyValue) {
                $value->$name = $this->hydrate($propertyValue);
            }

            return $value;
        }

        if (is_array($value)) {
            return array_map(function ($itemValue) {
                return $this->hydrate($itemValue);
            }, $value);
        }

        if (is_string($value)) {
            if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}(.*)$/', $value, $matches)) {
                if (strlen($matches[1]) === 0) {
                    return $this->dateTimeSerializer->deserialize($value, $this->dateSchema);
                }

                return $this->dateTimeSerializer->deserialize($value, $this->schema);
            }
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function dehydrate($value)
    {
        return $value;
    }
}
