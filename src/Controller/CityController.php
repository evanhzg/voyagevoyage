<?php

namespace App\Controller;

<<<<<<< HEAD
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
=======
use App\Entity\City;
use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Faker\Generator;
use Faker\Factory;
>>>>>>> 35c6d3c510f015e26f7af159de4861ebbf9a107c

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

<<<<<<< HEAD

    #[Route('/city', name: 'city.get', methode: ['POST'])]
    public function createCity(Request $request, EntityManager $entityManager) : JsonResponse
    {

=======
    /**
     * Get a response containing every city in the database
     * 
     * 
     * @param Country $country
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route("/api/cities", name: "city.getAll")]
    public function getAllPlaces(CityRepository $repository, SerializerInterface $serializerInterface): JsonResponse
    {
        $cities = $repository->findAll();
        $jsonCities = $serializerInterface->serialize($cities, 'json', ["groups" => 'getAllCities']);
        return new JsonResponse($jsonCities, Response::HTTP_OK,[], false);
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
>>>>>>> 35c6d3c510f015e26f7af159de4861ebbf9a107c
    }
}
