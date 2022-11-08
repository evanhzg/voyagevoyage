<?php

namespace App\Controller;

use Faker\Factory;
use App\Entity\City;
use Faker\Generator;
use Doctrine\ORM\EntityManager;
use App\Repository\CityRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * Getting all the cities names including adding and correcting it in the reposetory
     * 
     * 
     * @param City $city
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */  
    public function getAllCities(Request $request, CityRepository $cityRepository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $cities = $cityRepository->findWithPagination($page, $limit);
        $jsonCities = $cache->get("getAllCities", function (ItemInterface $item) use ($cities, $serializer) {
            $item->tag("citiesCache");
            return $serializer->serialize($cities, 'json', ["groups" => "getAllCities"]);
        });
        return new JsonResponse($jsonCities, Response::HTTP_OK, [], true);
    }

     /**
     * Adding a city name
     * 
     * 
     * @param City $city
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/city/', name: 'city.turnOff', methods: ['POST'])]  // here shouldn't it be city.creat ?
    public function addCity(Request $request, EntityManager $entityManager, SerializerInterface $serializer):JsonResponse
    {
        $city = $serializer->deserialize($request->getContent(), City::class, 'json');
        $city->setStatus(true);
        $entityManager->persist($city);
        $entityManager->flush();
        $jsonCity = $serializer->serialize($city, 'json');
        return new JsonResponse($jsonCity, Response::HTTP_CREATED, [], true);
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
    #[Route('/api/city', name: 'city.create', methods:['POST'])]
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
        $location = $urlGeneratorInterface->generate('city.get', ['idCity' => $city->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonCity , Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Path that updates a city
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
    #[Route('/api/city/{idCity}', name: 'city.update', methods:['PATCH'])]
    #[ParamConverter('city', options: ['id' => 'idCity'])]
    public function updateCountry(Request $request, City $city, CityRepository $cityRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGeneratorInterface, ValidatorInterface $validator): JsonResponse
    {
        if(!$city->isStatus()){
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, []);
        }

        $updateCity = $serializer->deserialize(
            $request->getContent(),
            City::class,
            'json',
        );
        $updateCity->setStatus(true);
        $content = $request->toArray();
        $city->setName($updateCity->getName() ?? $city->getName());
        $city->setDescription($updateCity->getDescriptopn() ?? $city->getDescription());
        $city->setPopulation($updateCity->getPopulation() ?? $city->getPopulation());
        $city->setEuropean($updateCity->isEuropean() ?? $city->isEuropean());
        $city->setLanguages($updateCity->getLanguages() ?? $city->getLanguages());
        $errors = $validator->validate($city);
        if($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);    
        }
        $entityManager->persist($city);
        $entityManager->flush();
        $context = SerializationContext::create()->setGroups(["getCity"]);
        $jsonCity = $serializer->serialize($city, 'json', $context);
        $location = $urlGeneratorInterface->generate('city.get', ['idCity' => $city->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonCity, Response::HTTP_CREATED, ["Location" => $location], true);
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

    /**
     * Path to deactivates a city
     * 
     * @param City $city
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[Route('/api/city/{idCity}', name: 'city.delete', methods:['DELETE'])]
    #[ParamConverter('country', options: ['id' => 'idCity'])]
    public function deactivateCity(City $city, EntityManagerInterface $entityManager): JsonResponse
    {
        $city->setStatus(false);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
    }

    
}
