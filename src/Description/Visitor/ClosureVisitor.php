<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Description\Visitor;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ClosureVisitor implements Visitor
{
    /**
     * @var \Closure
     */
    private $closure;

    /**
     * @var object
     */
    private $scope;

    /**
     * @param ClosureVisitorScope $scope
     * @param \Closure            $closure
     */
    public function __construct(ClosureVisitorScope $scope, \Closure $closure)
    {
        $this->closure = $closure;
        $this->scope   = $scope;
    }

    /**
     * @param Visitee $element
     *
     * @return mixed|void
     */
    public function visit(Visitee $element)
    {
        $this->closure->call($this->scope, $element);
    }
}
