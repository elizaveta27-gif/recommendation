<?php

namespace App\Validator;

abstract class BaseValidator
{
    public function __construct(protected ?self $validator = null)
    {
    }

    public function validate(array $data): array
    {
        $errors = $this->validator?->validate($data) ?? [];
        $errorsValidate =  $this->validateConcrete($data);
        
        return array_merge($errors, $errorsValidate);
    }
    
    abstract protected function validateConcrete(array $data): array;
    
}