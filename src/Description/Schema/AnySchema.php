<?php declare(strict_types=1);
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
class AnySchema extends Schema
{
    /**
     * ScalarSchema constructor.
     *
     * @param \stdClass $definition
     */
    public function __construct(\stdClass $definition = null)
    {
        $definition       = $definition ?: (object)[];
        $definition->type = Schema::TYPE_ANY;
        parent::__construct($definition);
    }
}
