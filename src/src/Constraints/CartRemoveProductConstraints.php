<?php

namespace App\Constraints;

use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Type;

class CartRemoveProductConstraints
{
    public static array $constraints = [];

    public static function getConstraints(): array
    {
        self::$constraints = [
            'id_product' => [
                new NotNull(),
                new Positive(),
                new Type('integer')
            ]
        ];

        return self::$constraints;
    }
}