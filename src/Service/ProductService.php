<?php

namespace App\Service;

use App\Entity\Product;
use App\Exception\CategoryNoFoundException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidateProductException;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Validator\PriceValidator;
use App\Validator\ProductBaseValidator;
use Doctrine\ORM\EntityManagerInterface;

class ProductService
{
    
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly ProductRepository $productRepository,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function add(array $data): void 
    {
        $errors = (new PriceValidator(
            (new ProductBaseValidator())
        ))->validate($data);

        if (!empty($errors)) {
            throw new ValidateProductException(json_encode($errors));
        }
        
        $category = $this->categoryRepository->findByCode($data['category']);

        if (empty($category)) {
            throw new CategoryNoFoundException('Category not found');
        }


        $product = new Product();
        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPrice($data['price']);
        $product->setUpdateAt(new \DateTime());
        
        $product->setAttributes($data['attributes']);

        $product->addCategory($category);

        $this->entityManager->persist($product);
        $this->entityManager->flush();
        
    }

    public function update(array $data): void
    {
        $errors = (new PriceValidator(new ProductBaseValidator()))->validate($data);

        if (!empty($errors)) {
            throw new ValidateProductException(json_encode($errors));
        }

        $product = $this->productRepository->find($data['id']);

        if ($product === null) {
            throw new ProductNotFoundException('Product not found');
        }
       
        $category = $this->categoryRepository->findByCode($data['category']);

        if (empty($category)) {
            throw new CategoryNoFoundException('Category not found');
        }

        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPrice($data['price']);
        $product->setUpdateAt(new \DateTime());
        $product->setAttributes($data['attributes']);

        $this->entityManager->flush();
    }

    public function delete(int $id): void
    {
        $product = $this->productRepository->find($id);

        if ($product === null) {
            throw new ProductNotFoundException('Product not found');
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();
    }
}