<?php

namespace App\Validator;

class RecommendationAttrValidator extends BaseValidator
{
    protected function validateConcrete(array $data): array
    {
        $errors = [];

        if (empty($data['product_id'])) {
            $errors[] = 'product_id is required';
        } elseif (!is_int($data['product_id']) || $data['product_id'] <= 0) {
            $errors[] = 'product_id must be a positive integer';
        }

        if (isset($data['limit'])) {
            if (!is_int($data['limit']) || $data['limit'] <= 0 || $data['limit'] > 100) {
                $errors[] = 'limit must be a positive integer and not greater than 100';
            }
        }

        return $errors;
    }
}
