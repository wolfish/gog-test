<?php

namespace App\Constraints;

use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;

class CartProductConstraints
{
    public static array $constraints = [];

    public static function getConstraints(): array
    {
        self::$constraints = [
            'id_product' => [
                new NotNull(),
                new Positive(),
                new Type('integer')
            ],

            'quantity' => [
                new NotNull(),
                new Positive(),
                new Range(min: 1, max: 10),
                new Type('integer')
            ]
        ];

        return self::$constraints;
    }
}