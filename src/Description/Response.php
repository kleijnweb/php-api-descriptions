<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Description;

use KleijnWeb\ApiDescriptions\Description\Visitor\VisiteeMixin;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class Response implements Element
{
    use VisiteeMixin;

    /**
     * @var int
     */
    protected $code;

    /**
     * @var Schema
     */
    protected $schema;

    /**
     * Response constructor.
     *
     * @param int       $code
     * @param \stdClass $definition
     */
    public function __construct(int $code, \stdClass $definition)
    {
        $this->code   = $code;
        $this->schema = Schema::get(isset($definition->schema) ? $definition->schema : null);
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return Schema
     */
    public function getSchema(): Schema
    {
        return $this->schema;
    }
}
