<?php

namespace App\Service;

use App\Exception\CategoryNoFoundException;
use App\Exception\DateIncorrectException;
use App\Exception\ProductNotFoundException;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use DateTimeImmutable;
use Predis\Client;

class ViewService
{
    //количество дней для хранения данных топ товаров
    public const TOP_COUNT_PRODUCTS_EXPIRE = 7;

    public const VIEW_HOUR_KEY = 'views:%s';
    public const USER_VIEW = 'view:user:%s';
    public const FORMAT_DATE_KEY = 'Y-m-d-H';
    public const TRENDS_UNION_CURRENT = 'views:union:current';
    public const TRENDS_UNION_PREV = 'views:union:prev';
    public const TRENDS_CACHE_KEY = 'cache:trends';
    
    public function __construct(
        private readonly Client $redis,
        private readonly CategoryRepository $categoryRepository,
        private readonly ProductRepository $productRepository,
    ) {
    }

    public function writeTop(int $productId, int $categoryId, string $date): void
    {
        if (!$this->categoryRepository->findById((int)$categoryId)) {
            throw new CategoryNoFoundException('Category not found');
        }
        
        if (!$this->productRepository->findById($productId)) {
            throw new CategoryNoFoundException('Product not found');
        }
        
        $date = DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $date);

        if ($date === false) {
            throw new DateIncorrectException('Invalid date');
        }
        
        $key = sprintf('category:%d:top:'. $date->format('d.m.Y'), $categoryId);
        $this->redis->zincrby($key, 1, (string) $productId);

        // ставим TTL только если ключ новый (ttl = -1 значит нет TTL)
        if ($this->redis->ttl($key) === -1) {
            $this->redis->expire($key, self::TOP_COUNT_PRODUCTS_EXPIRE * 24 * 60 * 60);
        }
    }

    public function writeTrend(int $productId, int $countView = 1, ?\DateTimeImmutable $date = null): void
    {
        if (!$this->productRepository->findById($productId)) {
            throw new ProductNotFoundException('Product not found');
        }

        $date ??= new DateTimeImmutable();
        $dateFormat = $date->format('Y-m-d-H');
        $key = sprintf(self::VIEW_HOUR_KEY, $dateFormat);
        $this->redis->zincrby($key, $countView, (string) $productId);
    }


    public function writeUserView(int $productId, int $userId): void
    {
        if (!$this->productRepository->findById($productId)) {
            throw new ProductNotFoundException('Product not found');
        }

        $listKey = sprintf(self::USER_VIEW, $userId);
        $setKey  = sprintf(self::USER_VIEW . ':set', $userId);

        if ($this->redis->sismember($setKey, (string) $productId)) {
            return;
        }

        $this->redis->lpush($listKey, [(string) $productId]);
        $this->redis->ltrim($listKey, 0, 9);
        $this->redis->sadd($setKey, [(string) $productId]);

        if ($this->redis->ttl($listKey) === -1) {
            $this->redis->expire($listKey, self::TOP_COUNT_PRODUCTS_EXPIRE * 24 * 60 * 60);
            $this->redis->expire($setKey, self::TOP_COUNT_PRODUCTS_EXPIRE * 24 * 60 * 60);
        }
    }
}
