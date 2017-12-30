<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Types;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class Pet
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $status;

    /**
     * @var float
     */
    private $price;

    /**
     * @var string[]
     */
    private $photoUrls;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var Tag[]
     */
    private $tags = [];

    /**
     * @var \stdClass
     */
    private $rating;

    /**
     * @param int       $id
     * @param string    $name
     * @param string    $status
     * @param float     $price
     * @param string[]  $photoUrls
     * @param Category  $category
     * @param Tag[]     $tags
     * @param \stdClass $rating
     */
    public function __construct(
        int $id,
        string $name,
        string $status,
        float $price,
        array $photoUrls = [],
        Category $category = null,
        array $tags = null,
        \stdClass $rating = null
    ) {
        $this->id        = $id;
        $this->name      = $name;
        $this->status    = $status;
        $this->price     = $price;
        $this->photoUrls = $photoUrls;
        $this->category  = $category;
        $this->tags      = $tags;
        $this->rating    = $rating;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return \string[]
     */
    public function getPhotoUrls(): array
    {
        return $this->photoUrls;
    }

    /**
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @return Tag[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @return \stdClass
     */
    public function getRating(): \stdClass
    {
        return $this->rating;
    }
}
