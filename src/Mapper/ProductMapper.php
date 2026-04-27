<?php

namespace App\Mapper;

use App\Dto\ProductDto;
use App\Entity\Product;

class ProductMapper
{
    public static function toDto(Product $product): ProductDto
    {
        return new ProductDto(
            id: $product->getId(),
            name:  $product->getName(),
            price: $product->getPrice(),
            attributes: $product->getAttributes() ?? [],
        );
    }
}