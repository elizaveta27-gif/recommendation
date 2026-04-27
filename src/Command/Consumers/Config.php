<?php

namespace App\Command\Consumers;

class Config
{
    //Очередь с ошибками при обработке операций с товарами
    public const PRODUCT_FAILED_QUEUE = 'product.failed';
    public const PRODUCT_CREATED_QUEUE = 'create_product';
    public const PRODUCT_UPDATED_QUEUE = 'update_product';
    public const PRODUCT_DELETED_QUEUE = 'delete_product';
    
    public const TOP_CATEGORY_VIEW = 'top_category_view';
    public const TRENDS_VIEW = 'trends_view';
    public const USER_VIEW = 'user_view';
    public const VIEW_FAILED_QUEUE = 'view.failed';
    public const VIEW_EVIEW_EXCHANGE = 'view.exchange';
    
    public const QUEUES = [
        self::PRODUCT_FAILED_QUEUE => [],
        'create_product.retry'  => [
            'arguments' => [
                'x-dead-letter-exchange'    => ['S', ''],
                'x-dead-letter-routing-key' => ['S', 'create_product'],
            ],
        ],
        self::PRODUCT_CREATED_QUEUE => [
            'arguments' => [
                'x-dead-letter-exchange'    => ['S', ''],
                'x-dead-letter-routing-key' => ['S', self::PRODUCT_FAILED_QUEUE],
            ],
        ],
        'update_product.retry' => [
            'arguments' => [
                'x-dead-letter-exchange'    => ['S', ''],
                'x-dead-letter-routing-key' => ['S', 'update_product'],
            ],
        ],
        self::PRODUCT_UPDATED_QUEUE => [
            'arguments' => [
                'x-dead-letter-exchange'    => ['S', ''],
                'x-dead-letter-routing-key' => ['S', self::PRODUCT_FAILED_QUEUE],
            ],
        ],
        'delete_product.retry' => [
            'arguments' => [
                'x-dead-letter-exchange'    => ['S', ''],
                'x-dead-letter-routing-key' => ['S', 'delete_product'],
            ],
        ],
        self::PRODUCT_DELETED_QUEUE => [
            'arguments' => [
                'x-dead-letter-exchange'    => ['S', ''],
                'x-dead-letter-routing-key' => ['S', self::PRODUCT_FAILED_QUEUE],
            ],
        ],
        self::VIEW_FAILED_QUEUE => [],
        self::TOP_CATEGORY_VIEW => [
            'arguments' => [
                'x-dead-letter-exchange'    => ['S', ''],
                'x-dead-letter-routing-key' => ['S', self::VIEW_FAILED_QUEUE],
            ],
            'exchange' => self::VIEW_EVIEW_EXCHANGE
        ],
        self::TRENDS_VIEW => [
            'arguments' => [
                'x-dead-letter-exchange'    => ['S', ''],
                'x-dead-letter-routing-key' => ['S', self::VIEW_FAILED_QUEUE],
            ],
            'exchange' => self::VIEW_EVIEW_EXCHANGE
        ],
        self::USER_VIEW => [
            'arguments' => [
                'x-dead-letter-exchange'    => ['S', ''],
                'x-dead-letter-routing-key' => ['S', self::VIEW_FAILED_QUEUE],
            ],
            'exchange' => self::VIEW_EVIEW_EXCHANGE
        ],
    ];

    public const EXCHANGERS = [
        self::VIEW_EVIEW_EXCHANGE => [
            'type' => 'fanout'
        ]
    ];
}