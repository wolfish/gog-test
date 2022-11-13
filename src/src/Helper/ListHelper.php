<?php

namespace App\Helper;


use App\Entity\GogCartHasProducts;

class ListHelper
{
    public static function getProductList(array $products): array
    {
        $processed = [];

        if (!empty($products)) {
            foreach ($products as $product) {
                $processed[$product->getId()]['title'] = $product->getTitle();
                $processed[$product->getId()]['price'] =
                    number_format($product->getPrice() / 100, 2) . ' ' .
                    $product->getCurrency()->getShortName();
            }
        }

        return $processed;
    }

    public static function getCartProductsList(array $products): array
    {
        $processed = [];
        $sum = [];

        if (!empty($products)) {
            foreach ($products as $product) {
                $cartHasProduct = $product;
                $product = $product->getProduct();

                $processed[$product->getId()]['title'] = $product->getTitle();
                $processed[$product->getId()]['price'] =
                    number_format($product->getPrice() / 100, 2) . ' ' .
                    $product->getCurrency()->getShortName();
                $processed[$product->getId()]['quantity'] = $cartHasProduct->getQuantity();

                $sum[$product->getCurrency()->getShortName()] ?? $sum[$product->getCurrency()->getShortName()] = 0;
                $sum[$product->getCurrency()->getShortName()] += $product->getPrice() * $cartHasProduct->getQuantity();
            }
        }


        foreach ($sum as $currency => $total) {
            $processed['total'][$currency] = number_format($total / 100, 2);
        }

        return $processed;
    }
}
