<?php

declare(strict_types=1);

namespace RulerZ\DoctrineORM\Target;

use Hoa\Ruler\Model as AST;

use RulerZ\Compiler\Context;
use RulerZ\Model;
use RulerZ\Target\GenericSqlVisitor;
use RulerZ\Target\Operators\Definitions as OperatorsDefinitions;

class DoctrineORMVisitor extends GenericSqlVisitor
{
    /**
     * @var DoctrineAutoJoin
     */
    private $autoJoin;

    public function __construct(Context $context, OperatorsDefinitions $operators, $allowStarOperator = true)
    {
        parent::__construct($context, $operators, $allowStarOperator);

        $this->autoJoin = new DoctrineAutoJoin($context['em'], $context['root_entities'], $context['root_aliases'], $context['joins']);
    }

    /**
     * {@inheritdoc}
     */
    public function getCompilationData(): array
    {
        return [
            'detectedJoins' => $this->autoJoin->getDetectedJoins(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function visitAccess(AST\Bag\Context $element, &$handle = null, $eldnah = null)
    {
        $dimensionNames = array_map(function ($dimension) {
            return $dimension[1];
        }, $element->getDimensions());
        array_unshift($dimensionNames, $element->getId());

        return $this->autoJoin->buildAccessPath($dimensionNames);
    }

    /**
     * {@inheritdoc}
     */
    public function visitParameter(Model\Parameter $element, &$handle = null, $eldnah = null)
    {
        // placeholder for a positional parameters
        if (is_int($element->getName())) {
            return '?'.$element->getName();
        }

        // placeholder for a named parameter
        return ':'.$element->getName();
    }
}
