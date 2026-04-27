<?php

namespace App\Service;

use App\Entity\Product;
use App\Exception\ProductNotFoundException;
use App\Mapper\ProductMapper;
use App\Repository\ProductRepository;
use App\Strategy\Product\Matching\StrategyMatching;

class ProductMatchingService
{
    private ?StrategyMatching $strategyMatching = null;
    
    public function __construct(
        private ProductRepository $productRepository,
    )
    {
    }
    
    public function setStrategy(StrategyMatching $strategyMatching)
    {
        $this->strategyMatching = $strategyMatching;
    }

    public function getProducts(int $productId, int $limit = 10): array
    {
        $product = $this->productRepository->findById($productId);

        if ($product === null) {
            throw new ProductNotFoundException('Product not found');
        }
        $order = $this->strategyMatching->getOrderClause($product->getAttributes()); 
        $filter = $this->strategyMatching->getWhereClause($product->getAttributes());

        $products = $this->productRepository->findByAttributes($filter, $order, $productId, $limit);
        return array_map(fn(Product $product) => ProductMapper::toDto($product)->toArray(), $products);
    }
}
