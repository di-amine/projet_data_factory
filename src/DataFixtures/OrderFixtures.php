<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use League\Csv\Reader;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $users = $manager->getRepository(User::class)->findAll();
        $products = $manager->getRepository(Product::class)->findAll();
        $faker = \Faker\Factory::create('fr_FR');

        for($i = 0; $i < 10000; $i++)
        {
            $o = new Order();

            $p = $products[array_rand($products)];
            $u = $users[array_rand($users)];
            $q = rand(1, 20);

            if (strpos($u->getUserAgent(), 'Mobile') !== false ||
                strpos($u->getUserAgent(), 'Android') !== false ||
                strpos($u->getUserAgent(), 'Phone') !== false)
            {
                continue;
            }

            $o
                ->setProduct($p)
                ->setQuantity($q)
                ->setUser($u)
                ->setDate($faker->dateTimeBetween('-1 month'))
            ;

            $manager->persist($o);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [ProductFixtures::class, UserFixtures::class];
    }
}
