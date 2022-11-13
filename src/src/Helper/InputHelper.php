<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InputHelper
{
    public static function decodeInput(Request $request): array
    {
        try {
            $parameters = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $validationErrors['error'] = 'Malformed JSON request';
            return $validationErrors;
        }

        return $parameters;
    }

    public static function validateInput(array $input, Collection $constraints, ValidatorInterface $validator): array
    {
        $validationErrors = [];

        $errors = $validator->validate($input, $constraints);
        if (count($errors)) {
            foreach ($errors as $error) {
                $validationErrors[$error->getPropertyPath()] = $error->getMessage();
            }
        }

        return $validationErrors;
    }
}
