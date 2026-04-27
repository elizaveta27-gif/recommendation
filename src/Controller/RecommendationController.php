<?php

namespace App\Controller;

use App\Factory\MatchingStrategyFactory;
use App\Service\ProductMatchingService;
use App\Service\TopCategoryService;
use App\Service\TrendsService;
use App\Service\UserView;
use App\Validator\RecommendationAttrValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: 'api/v1/recommendation')]
class RecommendationController extends AbstractController
{
    public function __construct(
        private readonly ProductMatchingService $productMatchingService,
        private readonly MatchingStrategyFactory $strategyFactory,
        private readonly RecommendationAttrValidator $validator,
        private readonly TrendsService $trendsService,
        private readonly TopCategoryService $topCategoryService,
        private readonly UserView $userView,
    ) {
    }

    #[Route(path: '/attr', name: 'product_similar_attr', methods: ['POST'])]
    public function attr(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validator->validate($data);
        if (!empty($errors)) {
            return $this->json(['errors' => $errors], 422);
        }

        $type = $data['match'] ?? 'all';
        $limit = $data['limit'] ?? 10;
        $this->productMatchingService->setStrategy($this->strategyFactory->create($type));
        $result = $this->productMatchingService->getProducts($data['product_id'], $limit);

        return $this->json($result);
    }
    
    #[Route(path: '/trends', name: 'trends', methods: ['GET'])]
    public function getTrends(Request $request): JsonResponse
    {
        return $this->json($this->trendsService->getTrends());
    }
    
    #[Route(path: '/top', name: 'top_category', methods: ['POST'])]
    public function getTopByCategory(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $categoryId = $data['category_id'] ?? null;

        if (!is_int($categoryId) || $categoryId <= 0) {
            return $this->json(['errors' => 'category_id is required and must be a positive integer'], 400);
        }

        return $this->json($this->topCategoryService->getPopularProducts($categoryId));
    }

    #[Route(path: '/user/view', name: 'user_view', methods: ['POST'])]
    public function getUserView(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $userId = $data['user_id'] ?? null;

        if (!is_int($userId) || $userId <= 0) {
            return $this->json(['errors' => 'user_id is required and must be a positive integer'], 400);
        }

        return $this->json($this->userView->getUserView($userId));
    }
    
}