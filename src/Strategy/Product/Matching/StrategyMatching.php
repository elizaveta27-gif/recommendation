<?php

namespace App\Strategy\Product\Matching;

interface StrategyMatching
{
    public function getType(): string;

    public function supports(string $type): bool;
    
    public function getWhereClause(array $attributes): string;
    
    public function getOrderClause(array $attributes): string;
}