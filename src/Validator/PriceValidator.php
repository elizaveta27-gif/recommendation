<?php

namespace App\Validator;

class PriceValidator extends BaseValidator
{
    private const MIN_PRICE = 0.01;
    private const MAX_PRICE = 1_000_000;

    protected function validateConcrete(array $data): array
    {
        $errors = [];

        if (!isset($data['price'])) {
            $errors['price'] = 'Price is required';
            return $errors;
        }

        if (!is_numeric($data['price'])) {
            $errors['price'] = 'Price must be a number';
            return $errors;
        }

        $price = (float) $data['price'];

        if ($price < self::MIN_PRICE) {
            $errors['price'] = sprintf('Price must be at least %s', self::MIN_PRICE);
        }

        if ($price > self::MAX_PRICE) {
            $errors['price'] = sprintf('Price must not exceed %s', self::MAX_PRICE);
        }

        return $errors;
    }
}
