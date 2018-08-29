<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Factory;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class FactoryQueue extends \SplPriorityQueue
{
    /**
     * @var int
     */
    protected $serial = PHP_INT_MAX;

    /**
     * PriorityQueue constructor.
     * @param Factory[] ...$factories
     */
    public function __construct(Factory ...$factories)
    {
        foreach ($factories as $factory) {
            $this->add($factory);
        }
    }

    /**
     * @param Factory $factory
     */
    public function add(Factory $factory)
    {
        $this->insert($factory, $factory->getPriority());
    }

    /**
     * @param mixed $value
     * @param mixed $priority
     */
    public function insert($value, $priority)
    {
        parent::insert($value, [$priority, $this->serial--]);
    }
}
