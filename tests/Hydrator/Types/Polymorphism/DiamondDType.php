<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Types\Polymorphism;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class DiamondDType extends DiamondAType
{
    /**
     * @var string
     */
    private $typeBProperty;

    /**
     * @var string
     */
    private $typeCProperty;

    /**
     * @var string
     */
    private $typeDProperty;

    /**
     * @return string
     */
    public function getTypeDProperty(): string
    {
        return $this->typeDProperty;
    }

    /**
     * @return string
     */
    public function getTypeBProperty(): string
    {
        return $this->typeBProperty;
    }

    /**
     * @return string
     */
    public function getTypeCProperty(): string
    {
        return $this->typeCProperty;
    }
}
