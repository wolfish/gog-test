<?php

namespace App\Constraints;


use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Type;

class PaginatorConstraints
{
    public static array $constraints = [];

    public static function getConstraints(): array
    {
        self::$constraints = [
            'page' => [
                new Positive(),
                new Type('integer')
            ]
        ];

        return self::$constraints;
    }
}