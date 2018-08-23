<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Factory;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\ProcessorBuilder;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Processor;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
interface Factory
{
    /**
     * @param Schema           $schema
     * @param ProcessorBuilder $builder
     * @return Processor|null
     */
    public function create(Schema $schema, ProcessorBuilder $builder);

    /**
     * @return int
     */
    public function getPriority(): int;
}
