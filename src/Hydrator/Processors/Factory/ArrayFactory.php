<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Factory;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ArraySchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\ProcessorBuilder;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\ArrayProcessor;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Processor;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ArrayFactory implements Factory
{
    const PRIORITY = 800;

    /**
     * @param Schema           $schema
     * @param ProcessorBuilder $builder
     * @return Processor|null
     */
    public function create(Schema $schema, ProcessorBuilder $builder)
    {
        if (!$schema instanceof ArraySchema) {
            return null;
        }

        return (new ArrayProcessor($schema))
            ->setItemsProcessor($builder->build($schema->getItemsSchema()));
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return self::PRIORITY;
    }
}
