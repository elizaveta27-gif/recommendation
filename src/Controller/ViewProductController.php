<?php

namespace App\Controller;

use App\Command\Consumers\Config;
use App\Producer\Producer;
use App\Validator\ViewDataAddValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: 'api/v1/product/view')]
class ViewProductController extends AbstractController
{
    public function __construct(
        private readonly Producer $producer,
    ) {
    }

    #[Route(path: '/', name: 'product_view_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $data['viewed_at'] = (new \DateTimeImmutable())->format('d.m.Y H:i:s');

        $errors = (new ViewDataAddValidator())->validate($data);

        if (!empty($errors)) {
            return $this->json(['errors' => $errors], 422);
        }

        $this->producer->sendExchange(json_encode($data), Config::VIEW_EVIEW_EXCHANGE);

        return $this->json(['status' => 'queued'], 202);
    }
}
