<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\City;
use App\Repository\CityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Faker\Generator;
use Faker\Factory;
// tooo tooooo

class CityController extends AbstractController
{
    #[Route('/city', name: 'app_city')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/CityController.php',
        ]);
    }


    /**
     * Get a city depending of the given id
     * 
     * 
     * @param City $city
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route("/api/cities/{idCity}", name: "city.get", methods: ['GET'])]
    #[ParamConverter("city", options: ["id" =>"idCity"])]
    public function getCity(City $city, SerializerInterface $serializer): JsonResponse
    {
        $jsonCity = $serializer->serialize($city, 'json', ["groups" => 'getCity']);
        
        return new JsonResponse($jsonCity, Response::HTTP_OK, ['accept' => 'jsons'], true);
    }

    /**
     * Deleting a city name
     * 
     * 
     * @param City $city
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */    
    #[Route('/api/city/{idCity}', name: 'city.delete', methods: ['DELETE'])]
    #[ParamConverter("city", options : ["id"=>"idCity"])]
    public function deleteCity(City $city, EntityManagerInterface $entityManager) :JsonResponse
    {
         $city->setStatus(false);
         $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);

    }

    
}
