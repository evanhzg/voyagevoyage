<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Faker\Factory;
use App\Entity\City;
use App\Entity\Place;
use App\Entity\Country;

class AppFixtures extends Fixture
{
    /**
     * Faker Generator
     * 
     * @var Generator
     */
    private Generator $faker;


    public function __construct()
    {
        $this->faker = Factory::create('en_GB');
    }

    public function load(ObjectManager $manager): void
    {
        for ($i=0; $i<8; $i++)
        {
            $country = new Country();
            $country->setName(ucfirst($this->faker->word()))
            ->setLanguage('fr_FR')
            ->setEuropean(random_int(0,1))
            ->setTimeZone('UTC+' . random_int(0, 14))
            ->setStatus(true);

            for ($j=0; $j<10; $j++)
            {
                $city = new City();
                $city->setName(ucfirst($this->faker->word()))
                ->setPopulation(random_int(13000, 850000))
                ->setDescription($this->faker->sentence(15))
                ->setCountry($country)
                ->setStatus(1);
                $manager->persist($city);
            
                for ($k=0; $k<10; $k++)
                {
                    $place = new Place();
                    $place->setName(ucfirst($this->faker->word()))
                    ->setType(ucfirst($this->faker->word()))
                    ->setAddress($this->faker->address())
                    ->setPricing(4)
                    ->setOpenHour($this->faker->dateTime())
                    ->setClosedHour($this->faker->dateTime())
                    ->setCity($city)
                    ->setStatus(1);
                    $manager->persist($place);
                }
            }
            $country->setCapital($city);
            $manager->persist($country);
            $manager->flush();
        }
    }
}