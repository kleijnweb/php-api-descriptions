<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Factory;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\AnySchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\DateTimeSerializer;
use KleijnWeb\PhpApi\Descriptions\Hydrator\ProcessorBuilder;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\AnyProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Processor;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class AnyFactory implements Factory
{
    const PRIORITY = 1000;

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
        if (!$schema instanceof AnySchema) {
            return null;
        }

        return new AnyProcessor(new AnySchema(), $this->dateTimeSerializer);
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return self::PRIORITY;
    }
}
