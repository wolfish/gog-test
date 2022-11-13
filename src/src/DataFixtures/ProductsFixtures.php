<?php

namespace App\DataFixtures;

use App\Entity\GogCurrency;
use App\Entity\GogProducts;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;

class ProductsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $currency = $this->getReference(CurrencyFixtures::USD_CURRENCY_REFERENCE);

        if (!$currency instanceof GogCurrency) {
            throw new Exception('Currency USD not defined');
        }

        $products = [
            [ 'title' => 'Fallout', 'price' => 199 ],
            [ 'title' => 'Don\'t Starve', 'price' => 299 ],
            [ 'title' => 'Baldur\'s Gate', 'price' => 399 ],
            [ 'title' => 'Icewind Dale', 'price' => 499 ],
            [ 'title' => 'Bloodborne', 'price' => 599 ]
        ];

        foreach ($products as $product) {
            $p = new GogProducts();
            $p->setTitle($product['title']);
            $p->setPrice($product['price']);
            $p->setCurrency($currency);
            $manager->persist($p);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CurrencyFixtures::class
        ];
    }
}
