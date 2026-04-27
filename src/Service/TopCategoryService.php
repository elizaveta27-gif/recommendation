<?php

namespace App\Service;

use App\Entity\Product;
use App\Mapper\ProductMapper;
use App\Repository\ProductRepository;
use Predis\Client;

class TopCategoryService
{
    public function __construct(
        private readonly Client $redis,
        private readonly ProductRepository $productRepository,
    ) {
    }

    public function getPopularProducts(int $categoryId)
    {
        $date = new \DateTime();
        $key = sprintf('category:%d:top:'. $date->format('d.m.Y'), $categoryId);

        $set = $this->redis->zrevrange($key, 0, 9, ['WITHSCORES' => true]);
        if (empty($set)) {
            return [];
        }
        
        $result = $this->productRepository->findBy(['id' => array_keys($set)]);

        return array_map(fn(Product $product) => ProductMapper::toDto($product)->toArray(), $result);
    }
    
}