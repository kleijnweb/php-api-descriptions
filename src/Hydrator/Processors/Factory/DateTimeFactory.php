<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Factory;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\DateTimeSerializer;
use KleijnWeb\PhpApi\Descriptions\Hydrator\ProcessorBuilder;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Processor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar\DateTimeProcessor;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class DateTimeFactory implements Factory
{
    const PRIORITY = 900;

    /**
     * @var DateTimeSerializer
     */
    protected $dateTimeSerializer;

    /**
     * DateTimeFactory constructor.
     * @param DateTimeSerializer $dateTimeSerializer
     */
    public function __construct(DateTimeSerializer $dateTimeSerializer)
    {
        $this->dateTimeSerializer = $dateTimeSerializer;
    }

    /**
     * @param Schema           $schema
     * @param ProcessorBuilder $builder
     * @return Processor|null
     */
    public function create(Schema $schema, ProcessorBuilder $builder)
    {
        if (!$schema instanceof ScalarSchema || !$schema->isDateTime()) {
            return null;
        }

        return new DateTimeProcessor($schema, $this->dateTimeSerializer);
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return self::PRIORITY;
    }
}
