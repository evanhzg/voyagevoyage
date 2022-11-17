<?php

namespace App\Controller;

use App\Entity\City;
use App\Repository\CityRepository;
use App\Repository\CountryRepository;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

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
        $orderBy = "id";
        $orderByDirection = "asc";
        $filters = [];
        if($request->get('alphabetical') !== null){
            $orderBy = "name";
        } else if('reverseAlphabetical' !== null){
            $orderBy = "name";
            $orderByDirection = "desc";
        }
        if($request->get('populationSortDesc') !== null) {
            $orderBy = "population";
            $orderByDirection = "desc";
        } else if($request->get('populationSortAsc')){
            $orderBy = "population";
        }
        if($request->get('name') !== null) {
            $filters["name"] = " LIKE '%" . $request->get('name') . "%'";
        }
        if($request->get('populationGreaterThan') != null && is_numeric($request->get('populationGreaterThan'))) {
            $filters["population"] = " > " . $request->get('populationGreaterThan');
        }
        if($request->get('populationLessThan') != null && is_numeric($request->get('populationLessThan'))) {
            $filters["population"] = " < " . $request->get('populationLessThan');
        }
        $cities = $cityRepository->findWithPagination($page, $limit, $orderBy, $orderByDirection, $filters);
        $context = SerializationContext::create()->setGroups(["getAllCities"]);
        $jsonCities = $serializer->serialize($cities, 'json', $context);
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
        $context = SerializationContext::create()->setGroups(["getCity"]);
        $jsonCity = $serializer->serialize($city, 'json', $context);
        return new JsonResponse($jsonCity, Response::HTTP_OK, ['accept' => 'jsons'], true);
    }
    
    /**
     * Path that creates a city then returns it
     * 
     * @param Request $request
     * @param CityRepository $cityRepository
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGeneratorInterface
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/cities', name: 'cities.create', methods:['POST'])]
    public function createCity(Request $request, CountryRepository $countryRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGeneratorInterface, ValidatorInterface $validator): JsonResponse
    {
        $city = $serializer->deserialize(
            $request->getContent(),
            City::class,
            'json'
        );
        $city->setStatus(true);
        $content = $request->toArray();
        $countryId = $content['countryId'] ?? 0;
        $city->setCountry($countryRepository->find($countryId));
        $errors = $validator->validate($city);
        if($errors->count() > 0 || $city->getCountry() == null){
            if($city->getCountry() == null){
                return new JsonResponse($serializer->serialize($errors, 'json')  . ', {"property_path": "countryId", "message": "You must attach the city to a country."}', Response::HTTP_BAD_REQUEST, [], true);    
            } else {
                return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);    
            }
        }
        $entityManager->persist($city);
        $entityManager->flush();
        $context = SerializationContext::create()->setGroups(["getCity"]);
        $jsonCity = $serializer->serialize($city, 'json', $context);
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
    public function updateCity(Request $request, City $city, CountryRepository $countryRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGeneratorInterface, ValidatorInterface $validator): JsonResponse
    {
        if(!$city->isStatus()){
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, []);
        }

        $updateCity = $serializer->deserialize(
            $request->getContent(),
            City::class,
            'json',
        );
        $city->setStatus(true);
        $content = $request->toArray();
        $city->setName($updateCity->getName() ?? $city->getName());
        $countryId = $content['countryId'] ?? $city->getCountry()->getId();
        $city->setCountry($countryRepository->find($countryId));
        $city->setPopulation($updateCity->getPopulation() ?? $city->getPopulation());
        $city->setDescription($updateCity->getDescription() ?? $city->getDescription());
        $errors = $validator->validate($city);
        if($errors->count() > 0 || $city->getCountry() == null){
            if($city->getCountry() == null){
                return new JsonResponse($serializer->serialize($errors, 'json')  . ', {"property_path": "countryId", "message": "You must attach the city to a country."}', Response::HTTP_BAD_REQUEST, [], true);    
            } else {
                return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);    
            }
        }
        $entityManager->persist($city);
        $entityManager->flush();
        $context = SerializationContext::create()->setGroups(["getCity"]);
        $jsonCity = $serializer->serialize($city, 'json', $context);
        $location = $urlGeneratorInterface->generate('cities.get', ['idCity' => $city->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
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
