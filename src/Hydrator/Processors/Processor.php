<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
abstract class Processor
{
    /**
     * @var Schema
     */
    protected $schema;

    /**
     * Hydrator constructor.
     * @param Schema $schema
     */
    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    abstract public function hydrate($value);

    /**
     * @param mixed $value
     * @return mixed
     */
    abstract public function dehydrate($value);
}
