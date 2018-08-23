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
use KleijnWeb\PhpApi\Descriptions\Hydrator\ProcessorBuilder;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Processor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar\BoolProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar\IntegerProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar\NullProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar\NumberProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar\StringProcessor;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ScalarFactory implements Factory
{
    const PRIORITY = 900;

    /**
     * @var array
     */
    protected static $primitiveMap = [
        Schema::TYPE_STRING => StringProcessor::class,
        Schema::TYPE_INT    => IntegerProcessor::class,
        Schema::TYPE_BOOL   => BoolProcessor::class,
        Schema::TYPE_NUMBER => NumberProcessor::class,
        Schema::TYPE_NULL   => NullProcessor::class,
    ];

    /**
     * @param Schema           $schema
     * @param ProcessorBuilder $builder
     * @return Processor|null
     */
    public function create(Schema $schema, ProcessorBuilder $builder)
    {
        if (!$schema instanceof ScalarSchema) {
            return null;
        }

        if (!isset(self::$primitiveMap[$schema->getType()])) {
            return null;
        }
        $className = self::$primitiveMap[$schema->getType()];

        return $hydrator = new $className($schema);
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return self::PRIORITY;
    }
}
