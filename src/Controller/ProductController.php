<?php

namespace App\Controller;

use App\Command\Consumers\Config;
use App\Producer\Producer;
use App\Validator\PriceValidator;
use App\Validator\ProductBaseValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: 'api/v1/product')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly Producer $producer,
    ) {
    }

    #[Route(path: '/', name: 'product_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        $errors = (new PriceValidator(new ProductBaseValidator()))->validate($data);

        if (!empty($errors)) {
            return $this->json(['errors' => $errors], 422);
        }

        $this->producer->sendQueue($content, Config::PRODUCT_CREATED_QUEUE);

        return $this->json(['status' => 'queued'], 202);
    }

    #[Route(path: '/{id}', name: 'product_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $id;

        $errors = (new PriceValidator(new ProductBaseValidator()))->validate($data);

        if (!empty($errors)) {
            return $this->json(['errors' => $errors], 422);
        }

        $this->producer->sendQueue(json_encode($data), Config::PRODUCT_UPDATED_QUEUE);

        return $this->json(['status' => 'queued'], 202);
    }

    #[Route(path: '/{id}', name: 'product_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->producer->sendQueue(json_encode(['id' => $id]), Config::PRODUCT_DELETED_QUEUE);

        return $this->json(['status' => 'queued'], 202);
    }
}
