<?php

namespace App\DataFixtures;

use App\Entity\GogCurrency;
use App\Entity\GogProducts;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;

class CurrencyFixtures extends Fixture
{

    public const USD_CURRENCY_REFERENCE = 'usd-currency';

    public function load(ObjectManager $manager): void
    {
        $currencies = [
            [ 'shortName' => 'PLN', 'fullName' => 'Polski złoty', 'symbol' => 'zł' ],
            [ 'shortName' => 'USD', 'fullName' => 'Dolar amerykański', 'symbol' => '$' ]
        ];

        foreach ($currencies as $currency) {
            $c = new GogCurrency();
            $c->setShortName($currency['shortName']);
            $c->setFullName($currency['fullName']);
            $c->setSymbol($currency['symbol']);
            $manager->persist($c);

            if ($c->getShortName() === 'USD') {
                $this->addReference(self::USD_CURRENCY_REFERENCE, $c);
            }
        }

        $manager->flush();
    }

}
