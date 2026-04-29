<?php

namespace App\Validator;


class ProductBaseValidator extends BaseValidator
{
    protected function validateConcrete(array $data): array
    {
        $errors = [];

        if (!isset($data['name']) || strlen(trim($data['name'])) < 3) {
            $errors['name'] = 'Product name is empty';
        }
        
        if (!isset($data['description']) || strlen(trim($data['description'])) < 3) {
            $errors['description'] = 'Description name is empty';
        }
        
        if (!isset($data['price']) || !is_numeric($data['price'])) {
            $errors['price'] = 'Price empty or invalid format';
        }
           
        if (!isset($data['category'])) {
            $errors['category'] = 'Category is empty';
        }

        if (!isset($data['attributes']) || !is_array($data['attributes'])) {
            $errors['attributes'] = 'Attributes is empty';
        } elseif (empty($data['attributes'])) {
            $errors['attributes'] = 'Attributes array is empty';
        }
        
        return $errors;
    }
}