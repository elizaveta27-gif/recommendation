<?php

namespace App\Dto;

use App\Entity\Product;

class ProductDto
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly float $price,
        private readonly array $attributes,
    )
    {
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'attributes' => $this->attributes,
        ];
    }

}