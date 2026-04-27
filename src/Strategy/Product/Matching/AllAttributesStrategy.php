<?php

namespace App\Strategy\Product\Matching;

class AllAttributesStrategy implements StrategyMatching
{

    public function getType(): string
    {
        return 'all';
    }
    
    public function getWhereClause(array $attributes): string
    {
        $conditions = [];
        foreach ($attributes as $name => $value) {
            $json = json_encode([$name => $value]);
            $conditions[] = "attributes @> '{$json}'::jsonb";
        }

        return !empty($conditions) ? implode(' AND ', $conditions) : '1=1';
    }

    public function getOrderClause(array $attributes): string
    {
        return 'id DESC';
    }

    public function supports(string $type): bool
    {
        return $type === $this->getType();
    }
}