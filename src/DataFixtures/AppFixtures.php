<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use Cocur\Slugify\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $colors    = ['black', 'white', 'red', 'blue', 'green', 'silver', 'gold'];
        $brands    = ['Samsung', 'Apple', 'Sony', 'LG', 'Bosch', 'Nike', 'Adidas'];
        $materials = ['plastic', 'metal', 'glass', 'wood', 'leather', 'fabric'];
        $sizes     = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        $slugify = new Slugify();
        $categories = [];
        for ($i = 0; $i < 200; $i++) {
            $category = new Category();
            $name = $faker->word();
            $category->setName($name);
            $category->setCode($slugify->slugify($name));
            if ($i > 0) {
                $category->setParent($categories[random_int(0, $i - 1)]);
            }
            $manager->persist($category);
            $categories[] = $category;
        }
        $manager->flush();
        for ($i = 0; $i < 15000; $i++) {
            $product = new Product();
            $product->setName($faker->words(3, true));
            $product->setDescription($faker->sentence(10));
            $product->setPrice((string) $faker->randomFloat(2, 10, 1000));
            $product->setUpdateAt(new \DateTime());
            $product->addCategory($categories[random_int(0, count($categories) - 1)]);
            $product->setAttributes([
                'color'    => $faker->randomElement($colors),
                'brand'    => $faker->randomElement($brands),
                'material' => $faker->randomElement($materials),
                'size'     => $faker->randomElement($sizes),
                'weight'   => $faker->randomFloat(2, 0.1, 10) . 'kg',
                'rating'   => $faker->randomFloat(1, 1, 5),
            ]);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
