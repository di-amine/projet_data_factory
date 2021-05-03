<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $items = [
            'accessoire' => 'Accessoires',
            'cours' => 'Cours',
            'rose' => 'Vins rosés',
            'rouge' => 'Vins rouges',
            'blanc' => 'Vins blancs'
        ];

        foreach($items as $k => $item)
        {
            $c = (new Category())->setTitle($item);

            $this->addReference('cat/' . $k, $c);

            $manager->persist($c);
        }

        $manager->flush();
    }
}
