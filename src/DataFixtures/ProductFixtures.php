<?php

namespace App\DataFixtures;

use App\Entity\Product;
use League\Csv\Reader;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $csv = Reader::createFromPath('/Volumes/Data/tom/Downloads/products.csv', 'r');
        $csv->setHeaderOffset(0);

        $records = $csv->getRecords();

        foreach($records as $item)
        {
            $category = $this->getReference('cat/' . $item['category']);

            $p = new Product();

            $p
                ->setTitle($item['title'])
                ->setReference($item['reference'])
                ->setPrice($item['price_sell'])
                ->setCategory($category)
            ;

            $manager->persist($p);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [CategoryFixtures::class];
    }
}
