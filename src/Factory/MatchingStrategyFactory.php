<?php

namespace App\Factory;

use App\Strategy\Product\Matching\StrategyMatching;

class MatchingStrategyFactory
{
    /** @var StrategyMatching[] */
    private array $strategies;

    public function __construct(
        #[\Symfony\Component\DependencyInjection\Attribute\TaggedIterator('matching.strategy')]
        iterable $strategies,
    ) {
        foreach ($strategies as $strategy) {
            $this->strategies[$strategy->getType()] = $strategy;
        }
    }

    public function create(string $type): StrategyMatching
    {
        if (!isset($this->strategies[$type])) {
            throw new \InvalidArgumentException(sprintf('Unknown matching strategy: "%s"', $type));
        }

        return $this->strategies[$type];
    }
}
