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
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

/**
 * Routes related to cities.
 * @OA\Tag(name="Cities")
 */
class CityController extends AbstractController
{
    #[Route('/city', name: 'app_city')]
    public function index(Request $request, SerializerInterface $hateoas): JsonResponse
    {
        return $this->json([
            'message' => "Wrong route, try 'http://" . explode('/', $request->getUri())[2] . "/api/cities'",
        ]);
    }

    /**
     * Path that returns all cities
     *  
     * @OA\Response(
     *     response=200,
     *     description="Array of cities",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=City::class, groups={"getAllCities"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="The number of cities per page, by default 10",
     *     @OA\Schema(type="int")
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="The page you're at, by default 1",
     *     @OA\Schema(type="int")
     * )
     * @OA\Parameter(
     *     name="alphabetical",
     *     in="query",
     *     description="Sort by name in alphabetical order. Put any value to sort",
     * )
     * @OA\Parameter(
     *     name="reverseAlphabetical",
     *     in="query",
     *     description="Sort by name in reverse alphabetical order. Put any value to sort",
     * )
     * @OA\Parameter(
     *     name="populationSortDesc",
     *     in="query",
     *     description="Sort by population, in descending order. Put any value to sort",
     * )
     * @OA\Parameter(
     *     name="populationSortAsc",
     *     in="query",
     *     description="Sort by population, in descending order. Put any value to sort",
     * )
     * @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="Search names that match value",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="countryId",
     *     in="query",
     *     description="Get cities for one country",
     *     @OA\Schema(type="int")
     * )
     * @OA\Parameter(
     *     name="populationGreaterThan",
     *     in="query",
     *     description="Search cities that has more population than value",
     *     @OA\Schema(type="int")
     * )
     * @OA\Parameter(
     *     name="populationLessThan",
     *     in="query",
     *     description="Search cities that has less population than value",
     *     @OA\Schema(type="int")
     * )
     * 
     * @param Request $request
     * @param CityRepository $cityRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/cities', name: 'cities.getAll', methods: ['GET'])]
    public function getAllCities(Request $request, CityRepository $cityRepository, SerializerInterface $serializer): JsonResponse
    {
        $page = $request->get('page') > 0 ? $request->get('page') : 1;
        $limit = $request->get('limit') > 0 ? $request->get('limit') : 10;
        $orderBy = "id";
        $orderByDirection = "asc";
        $filters = [];
        if($request->get('alphabetical') !== null){
            $orderBy = "name";
        } else if($request->get('reverseAlphabetical') !== null){
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
        if($request->get('countryId') !== null){
            $filters["country"] = " = " . $request->get('countryId');            
        }
        if($request->get('populationGreaterThan') != null && is_numeric($request->get('populationGreaterThan'))) {
            $filters["population"] = " > " . $request->get('populationGreaterThan');
        }
        if($request->get('populationLessThan') != null && is_numeric($request->get('populationLessThan'))) {
            $filters["population"] = " < " . $request->get('populationLessThan');
        }
        $cities = $cityRepository->findWithPagination($page, $limit, $orderBy, $orderByDirection, $filters);
        $context = SerializationContext::create()->setGroups(["getAllCities"])->setSerializeNull(true);
        $jsonCities = $serializer->serialize($cities, 'json', $context);
        return new JsonResponse($jsonCities, Response::HTTP_OK, [], true);
    }

    /**
     * Path that returns one city by its id
     * 
     * @OA\Response(
     *     response=200,
     *     description="The city with the id 'idCity'",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=City::class, groups={"getCity"}))
     *     )
     * )
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
        $context = SerializationContext::create()->setGroups(["getCity"])->setSerializeNull(true);
        $jsonCity = $serializer->serialize($city, 'json', $context);
        return new JsonResponse($jsonCity, Response::HTTP_OK, ['accept' => 'jsons'], true);
    }
    
    /**
     * Path that creates a city then returns it
     * 
     * @OA\Response(
     *     response=201,
     *     description="The city created",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=City::class, groups={"getCity"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="name",
     *     in="query",
     *     required=true,
     *     description="Name of the city",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="countryId",
     *     in="query",
     *     required=true,
     *     description="The id of the country the city is in",
     *     @OA\Schema(type="int")
     * )
     * @OA\Parameter(
     *     name="population",
     *     in="query",
     *     description="The number of people in the city",
     *     @OA\Schema(type="int")
     * )
     * @OA\Parameter(
     *     name="description",
     *     in="query",
     *     description="The description of the city",
     *     @OA\Schema(type="text")
     * )
     * @OA\Parameter(
     *     name="time_zone",
     *     in="query",
     *     description="The time zone the city is in, in format 'UTC+X'",
     *     @OA\Schema(type="string")
     * )
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
        $context = SerializationContext::create()->setGroups(["getCity"])->setSerializeNull(true);
        $jsonCity = $serializer->serialize($city, 'json', $context);
        $location = $urlGeneratorInterface->generate('cities.get', ['idCity' => $city->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonCity, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Path that updates a city then returns it
     * 
     * @OA\Response(
     *     response=201,
     *     description="The city updated",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=City::class, groups={"getCity"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="Name of the city",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="countryId",
     *     in="query",
     *     description="The id of the country the city is in",
     *     @OA\Schema(type="int")
     * )
     * @OA\Parameter(
     *     name="population",
     *     in="query",
     *     description="The number of people in the city",
     *     @OA\Schema(type="int")
     * )
     * @OA\Parameter(
     *     name="description",
     *     in="query",
     *     description="The description of the city",
     *     @OA\Schema(type="text")
     * )
     * @OA\Parameter(
     *     name="time_zone",
     *     in="query",
     *     description="The time zone the city is in, in format 'UTC+X'",
     *     @OA\Schema(type="string")
     * )
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
        $context = SerializationContext::create()->setGroups(["getCity"])->setSerializeNull(true);
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
     * @OA\Response(
     *     response=204,
     *     description="Empty city"
     * )
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
