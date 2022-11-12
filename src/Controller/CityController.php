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
    public function index(Request $request): JsonResponse
    {
        return $this->json([
            'message' => "Wrong route, try 'http://" . explode('/', $request->getUri())[2] . "/api/cities'",
        ]);
    }

    /**
     * Path that returns all cities
     * 
     * @param Request $request
     * @param CityRepository $cityRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/cities', name: 'cities.getAll', methods: ['GET'])]
    public function getAllCities(Request $request, CityRepository $cityRepository, SerializerInterface $serializer): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $cities = $cityRepository->findWithPagination($page, $limit);
        $jsonCities = $serializer->serialize($cities, 'json', ["groups" => "getAllCities"]);
        return new JsonResponse($jsonCities, Response::HTTP_OK, [], true);
    }

    /**
     * Path that returns one city by its id
     * 
     * @param City $city
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/cities/{idCity}', name: 'cities.get', methods:['GET'])]
    #[ParamConverter('city', options: ['id' => 'idCity'])]
    public function getCity(City $city, SerializerInterface $serializer): JsonResponse
    {
        if(!$city->isStatus()){
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, []);
        }
        $jsonCity = $serializer->serialize($city, 'json', ["groups" => "getCity"]);
        return new JsonResponse($jsonCity, Response::HTTP_OK, ['accept' => 'jsons'], true);
    }
    
    /**
     * Path that creates a city then returns it
     * 
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGeneratorInterface
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/cities', name: 'cities.create', methods:['POST'])]
    public function createCity(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGeneratorInterface, ValidatorInterface $validator): JsonResponse
    {
        $city = $serializer->deserialize(
            $request->getContent(),
            City::class,
            'json'
        );
        $city->setStatus(true);
        $errors = $validator->validate($city);
        if($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);    
        }
        $entityManager->persist($city);
        $entityManager->flush();
        $jsonCity = $serializer->serialize($city, 'json', ['groups' => "getCity"]);
        $location = $urlGeneratorInterface->generate('cities.get', ['idCity' => $city->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonCity, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Path that updates a city then returns it
     * 
     * @param Request $request
     * @param City $city
     * @param CityRepository $cityRepository
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGeneratorInterface
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/cities/{idCity}', name: 'cities.update', methods:['PATCH'])]
    #[ParamConverter('city', options: ['id' => 'idCity'])]
    public function updateCity(Request $request, City $city, CityRepository $cityRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGeneratorInterface, ValidatorInterface $validator): JsonResponse
    {
        if(!$city->isStatus()){
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, []);
        }

        $updateCity = $serializer->deserialize(
            $request->getContent(),
            City::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $city] 
        );
        $updateCity->setStatus(true);
        $errors = $validator->validate($city);
        if($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);    
        }
        $entityManager->persist($city);
        $entityManager->flush();
        $jsonCity = $serializer->serialize($updateCity, 'json', ['groups' => "getCity"]);
        $location = $urlGeneratorInterface->generate('cities.get', ['idCity' => $updateCity->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonCity, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Methods that deletes a city (no path)
     * 
     * @param City $city
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[ParamConverter('city', options: ['id' => 'idCity'])]
    public function deleteCity(City $city, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($city);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
    }

    /**
     * Path that deactivates a city
     * 
     * @param City $city
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[Route('/api/cities/{idCity}', name: 'cities.delete', methods:['DELETE'])]
    #[ParamConverter('city', options: ['id' => 'idCity'])]
    public function deactivateCity(City $city, EntityManagerInterface $entityManager): JsonResponse
    {
        $city->setStatus(false);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
    }
}
