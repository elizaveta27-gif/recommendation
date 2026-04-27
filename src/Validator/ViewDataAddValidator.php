<?php

namespace App\Validator;

class ViewDataAddValidator extends BaseValidator
{
    protected function validateConcrete(array $data): array
    {
        $errors = [];

        if (!isset($data['product_id']) || !is_int($data['product_id'])) {
            $errors['product_id'] = 'product_id is required and must be an integer';
        }

        if (!isset($data['user_id']) || !is_int($data['user_id'])) {
            $errors['user_id'] = 'user_id is required and must be an integer';
        }
        
        if (!isset($data['category_id']) || !is_int($data['category_id'])) {
            $errors['category_id'] = 'category_id is required and must be an integer';
        }

        return $errors;
    }
}
