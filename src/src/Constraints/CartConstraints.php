<?php

namespace App\Constraints;


use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;

class CartConstraints
{
    public static array $constraints = [];

    public static function getConstraints(): array
    {
        self::$constraints = [
            'id_session' => [
                new Length(min: 1, max: 255),
                new NotBlank(),
                new NotNull(),
                new Type('string')
            ]
        ];

        return self::$constraints;
    }
}