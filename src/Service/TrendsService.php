<?php

namespace App\Service;

use App\Entity\Product;
use App\Mapper\ProductMapper;
use App\Repository\ProductRepository;
use DateInterval;
use Predis\Client;

class TrendsService
{
    public function __construct(
        private readonly Client $redis,
        private readonly ProductRepository $productRepository,
    )
    {
    }
    
    public function generateTrends(): array
    {
        $prev = [];
        $current = [];
        $date = new \DateTime();

        for($i = 0; $i < 3; $i++) 
        {
            $current[] = sprintf(ViewService::VIEW_HOUR_KEY, $date->format(ViewService::FORMAT_DATE_KEY));
            $date->sub(new DateInterval("PT1H"));
        }

        $this->redis->zunionstore(ViewService::TRENDS_UNION_CURRENT, $current);

        for($i = 0; $i < 3; $i++)
        {
            $prev[] = sprintf(ViewService::VIEW_HOUR_KEY, $date->format(ViewService::FORMAT_DATE_KEY));
            $date->sub(new DateInterval("PT1H"));
        }

        $this->redis->zunionstore(ViewService::TRENDS_UNION_PREV, $prev);
        
        $step = 500;
        $offset = 0;
        $result = [];

        while (count($result) < 10) {
            $set = $this->redis->zrevrange(ViewService::TRENDS_UNION_CURRENT, $offset, $offset + $step - 1, ['WITHSCORES' => true]);

            if (empty($set)) {
                break;
            }

            $productIds = array_keys($set);

            $scores = $this->redis->pipeline(function ($pipe) use ($productIds) {
                foreach ($productIds as $productId) {
                    $pipe->zscore(ViewService::TRENDS_UNION_PREV, (string) $productId);
                }
            });

            foreach ($productIds as $i => $productId) {
                $prevScore = $scores[$i];
                $item = $set[$productId];

                if (is_numeric($prevScore) && $prevScore > 0 && $item / $prevScore > 1.5) {
                    $result[] = (int) $productId;
                }

                if (count($result) >= 10) {
                    break 2;
                }
            }

            $offset += $step;
        }

        $this->redis->set(ViewService::TRENDS_CACHE_KEY, json_encode($result));

        $this->redis->del([ViewService::TRENDS_UNION_CURRENT]);
        $this->redis->del([ViewService::TRENDS_UNION_PREV]);
        
        return $result;
    }
    
    public function getTrends(): array
    {
        $trends = $this->redis->get(ViewService::TRENDS_CACHE_KEY);
      
        if (empty($trends)) {
            return [];
        }

        $ids = json_decode($trends, true);
        
        $result = $this->productRepository->findBy(['id' => $ids]);

        return array_map(fn(Product $product) => ProductMapper::toDto($product)->toArray(), $result);
    }
    
}