<?php

namespace App\Strategy\Product\Matching;

use App\Enum\AttributePriority;
use App\Repository\AttributeSchemaRepository;

class PriorityAttributesStrategy implements StrategyMatching
{
    private array $schema;

    public function __construct(AttributeSchemaRepository $repository)
    {
        foreach ($repository->findAll() as $attr) {
            $this->schema[$attr->getCode()] = $attr->getPriority();
        }
    }

    public function getType(): string
    {
        return 'priority';
    }

    public function supports(string $type): bool
    {
        return $type === $this->getType();
    }
    
    public function getWhereClause(array $attributes): string
    {
        $conditions = [];
        foreach ($attributes as $name => $value) {
            $priority = $this->schema[$name] ?? AttributePriority::Optional;
            if ($priority === AttributePriority::Important) {
                $json = json_encode([$name => $value]);
                $conditions[] = "attributes @> '{$json}'::jsonb";
            }
        }
        return !empty($conditions) ? implode(' AND ', $conditions) : '1=1';
    }

    public function getOrderClause(array $attributes): string
    {
        $cases = [];
        foreach ($attributes as $name => $value) {
            $priority = $this->schema[$name] ?? AttributePriority::Optional;
            $weight = $priority->getWeight();
            $json = json_encode([$name => $value]);
            $cases[] = "CASE WHEN attributes @> '{$json}'::jsonb THEN {$weight} ELSE 0 END";
        }

        return !empty($cases) ? '(' . implode(' + ', $cases) . ') DESC' : 'id DESC';
    }
   
}