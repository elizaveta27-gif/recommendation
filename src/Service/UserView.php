<?php

namespace App\Service;

use App\Entity\Product;
use App\Mapper\ProductMapper;
use App\Repository\ProductRepository;
use Predis\Client;

class UserView
{
    public function __construct(
        private readonly Client $redis,
        private readonly ProductRepository $productRepository,
    ) {
    }

    public function getUserView(int $userId)
    {
        $key = sprintf(ViewService::USER_VIEW, $userId);

        $set = $this->redis->lrange($key, 0, 10);
        if (empty($set)) {
            return [];
        }

        $result = $this->productRepository->findBy(['id' => $set]);

        return array_map(fn(Product $product) => ProductMapper::toDto($product)->toArray(), $result);
    }
    
}