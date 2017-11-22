<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description\Visitor;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
trait VisiteeMixin
{
    /**
     * @param Visitor $visitor
     *
     * @return void
     */
    public function accept(Visitor $visitor)
    {
        foreach ($this as $attribute => $value) {
            foreach ($this->isTraversable($value) ? $value : [$value] as $element) {
                if ($element instanceof Visitee) {
                    $element->accept($visitor);
                }
            }
        }

        /** @noinspection PhpParamsInspection */
        $visitor->visit($this);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    private function isTraversable($value): bool
    {
        return is_array($value) || $value instanceof \stdClass || $value instanceof \Traversable;
    }
}
