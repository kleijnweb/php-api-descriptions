<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description\Respository;

use KleijnWeb\PhpApi\Descriptions\Description\Repository;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RepositoryIterator implements \Iterator
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->repository->get($this->key());
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->repository->getUris()[$this->position];
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return isset($this->repository->getUris()[$this->position]);
    }
}
