<?php

namespace PhpSpec\Iterator;

use PhpSpec\Runner\Maintainer\MaintainerInterface;
use PhpSpec\Loader\Node\ExampleNode;
use PhpSpec\SpecificationInterface;
use PhpSpec\Runner\MatcherManager;
use PhpSpec\Runner\CollaboratorManager;
use PhpSpec\Wrapper\Unwrapper;
use PhpSpec\Iterator\Collaborator;
use ReflectionClass;
use ReflectionException;
use Prophecy\Prophet;

final class Maintainer implements MaintainerInterface
{
    private $unwrapper;

    public function __construct(Unwrapper $unwrapper)
    {
        $this->unwrapper = $unwrapper;
    }

    public function supports(ExampleNode $example)
    {
        return true;
        return $this->hasAnyIterator($example);
    }

    public function prepare(ExampleNode $example, SpecificationInterface $context, MatcherManager $matchers, CollaboratorManager $collaborators)
    {
        $this->prophet = new Prophet(null, $this->unwrapper, null);
        foreach ($this->getIterators($example) as $name => $class) {
            $collaborators->set($name, new Collaborator($this->prophet->prophesize(), $class));
        }
    }

    public function teardown(ExampleNode $example, SpecificationInterface $context, MatcherManager $matchers, CollaboratorManager $collaborators)
    {
        $this->prophet->checkPredictions();
    }

    public function getPriority()
    {
        return 40;
    }

    private function hasAnyIterator(ExampleNode $example)
    {
        return 0 > count(iterator_to_array($this->getIterators($example)));
    }

    private function getIterators(ExampleNode $example)
    {
        $classRefl = $example->getSpecification()->getClassReflection();

        if ($classRefl->hasMethod('let')) {
            foreach ($classRefl->getMethod('let')->getParameters() as $parameter) {
                if ($this->isIterator($parameter->getClass())) {
                    yield $parameter->getName() => $parameter->getClass()->getName();
                }
            }
        }
        foreach ($example->getFunctionReflection()->getParameters() as $parameter) {
            if ($this->isIterator($parameter->getClass())) {
                yield $parameter->getName() => $parameter->getClass()->getName();
            }
        }
    }

    private function isIterator($class)
    {
        try {
            if (!$class instanceof ReflectionClass) {
                $class = new ReflectionClass($class);
            }
            return $class->implementsInterface('Iterator');
        }
        catch (ReflectionException $e) {
        }

        return false;
    }
}
