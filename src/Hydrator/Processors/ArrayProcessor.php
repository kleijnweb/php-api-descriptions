<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\ArraySchema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ArrayProcessor extends Processor
{
    /**
     * @var Processor
     */
    protected $itemsProcessor;

    /**
     * ArrayHydrator constructor.
     * @param ArraySchema $schema
     */
    public function __construct(ArraySchema $schema)
    {
        parent::__construct($schema);
    }

    /**
     * @param Processor $itemsHydrator
     * @return ArrayProcessor
     */
    public function setItemsProcessor(Processor $itemsHydrator): ArrayProcessor
    {
        $this->itemsProcessor = $itemsHydrator;

        return $this;
    }

    /**
     * @param null|array $value
     * @return null|array
     */
    public function hydrate($value)
    {
        if ($value === null) {
            if (null == ($value = $this->schema->getDefault())) {
                return null;
            }
        }

        return array_map(function ($value) {
            return $this->itemsProcessor->hydrate($value);
        }, $value);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function dehydrate($value)
    {
        return array_map(function ($value) {
            return $this->itemsProcessor->dehydrate($value);
        }, $value);
    }
}
