<?php

namespace App\Constraints;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Type;

class ProductConstraints
{
    public static array $constraints = [];

    public static function getConstraints(): array
    {
        self::$constraints = [
            'title' => [
                new NotBlank(),
                new NotNull(),
                new Length(max: 255),
                new Type('string')
            ],
            'price' => [
                new PositiveOrZero(),
                new Type(type: 'integer')
            ],
            'currency' => [
                new NotBlank(),
                new NotNull(),
                new Length(3),
                new Type('string')
            ]
        ];

        return self::$constraints;
    }
}