<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\PhpApi\Descriptions\Description\Schema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ArraySchema extends Schema
{
    /**
     * @var Schema|null
     */
    protected $itemsSchema;

    /**
     * ArraySchema constructor.
     *
     * @param \stdClass   $definition
     * @param Schema|null $itemsSchema
     */
    public function __construct(\stdClass $definition, $itemsSchema)
    {
        $definition->type = Schema::TYPE_ARRAY;
        $this->itemsSchema = $itemsSchema;
        parent::__construct($definition);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return Schema::TYPE_ARRAY;
    }

    /**
     * @return Schema
     */
    public function getItemsSchema(): Schema
    {
        return $this->itemsSchema;
    }
}
