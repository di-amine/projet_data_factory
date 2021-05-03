<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');

        $testGroups = ['a','b','c'];

        for ($i = 0; $i < 1000; $i++)
        {
            $user = new User();

            $user
                ->setTitle($faker->name())
                ->setCity($faker->city())
                ->setCountry($faker->country())
                ->setTestGroup($testGroups[array_rand($testGroups)])
                ->setUserAgent($faker->userAgent())
                ->setIp($faker->ipv4())
            ;

            $manager->persist($user);
        }

        $manager->flush();
    }
}
