<?php 
namespace App\Service;

class ValidationService
{
    public function validateLoginData(array $data): array
    {
        $errors = [];
        if (empty($data['username'])) {
            $errors[] = 'Username is required.';
        }
        if (empty($data['password'])) {
            $errors[] = 'Password is required.';
        }
        return $errors;
    }
}
