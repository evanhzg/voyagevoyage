<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Faker\Factory;
use App\Entity\City;
use App\Entity\Place;
use App\Entity\Country;
use DateInterval;

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
            ->setLanguages('fr_FR')
            ->setEuropean(random_int(0,1))
            ->setStatus(true);
            $countryTimeZone = random_int(0, 14);
            for ($j=0; $j<10; $j++)
            {
                $city = new City();
                $city->setName(ucfirst($this->faker->word()))
                ->setPopulation(random_int(13000, 850000))
                ->setDescription($this->faker->sentence(15))
                ->setCountry($country)
                ->setTimeZone('UTC+' . $countryTimeZone)
                ->setStatus(1);
                $manager->persist($city);
            
                for ($k=0; $k<10; $k++)
                {
                    $place = new Place();
                    $place->setName(ucfirst($this->faker->word()))
                    ->setType(ucfirst($this->faker->word()))
                    ->setAddress($this->faker->address())
                    ->setPricing($this->faker->numberBetween(1, 3))
                    ->setOpenHour('08:00')
                    ->setClosedHour('18:00')
                    ->setOpenDays("Monday, Tuesday, Wednesday, Thursday, Friday")
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