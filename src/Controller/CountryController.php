<?php

namespace App\Controller;

use App\Entity\Country;
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

class CountryController extends AbstractController
{
    #[Route('/country', name: 'app_country')]
    public function index(Request $request): JsonResponse
    {
        return $this->json([
            'message' => "Wrong route, try 'http://" . explode('/', $request->getUri())[2] . "/api/countries'",
        ]);
    }

    /**
     * Path that returns all countries
     * 
     * @param Request $request
     * @param CountryRepository $countryRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/countries', name: 'countries.getAll', methods: ['GET'])]
    public function getAllCountries(Request $request, CountryRepository $countryRepository, SerializerInterface $serializer): JsonResponse
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
        if($request->get('european') === "true" || $request->get('european') === "false") {
            $filters["european"] = " = " . $request->get('european');
        }
        if($request->get('name') !== null) {
            $filters["name"] = " LIKE '%" . $request->get('name') . "%'";
        }
        if($request->get('language') !== null) {
            $filters["languages"] = " LIKE '%" . $request->get('language') . "%'";
        }
        $countries = $countryRepository->findWithPagination($page, $limit, $orderBy, $orderByDirection, $filters);
        $context = SerializationContext::create()->setGroups(["getAllCountries"]);
        $jsonCountries = $serializer->serialize($countries, 'json', $context);
        return new JsonResponse($jsonCountries, Response::HTTP_OK, [], true);
    }

    /**
     * Path that returns one country by its id
     * 
     * @param Country $country
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/countries/{idCountry}', name: 'countries.get', methods:['GET'])]
    #[ParamConverter('country', options: ['id' => 'idCountry'])]
    public function getCountry(Country $country, SerializerInterface $serializer): JsonResponse
    {
        if(!$country->isStatus()){
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, []);
        }
        $context = SerializationContext::create()->setGroups(["getCountry"]);
        $jsonCountry = $serializer->serialize($country, 'json', $context);
        return new JsonResponse($jsonCountry, Response::HTTP_OK, ['accept' => 'jsons'], true);
    }
    
    /**
     * Path that creates a country then returns it
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
    #[Route('/api/countries', name: 'countries.create', methods:['POST'])]
    public function createCountry(Request $request, CityRepository $cityRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGeneratorInterface, ValidatorInterface $validator): JsonResponse
    {
        $country = $serializer->deserialize(
            $request->getContent(),
            Country::class,
            'json'
        );
        $country->setStatus(true);
        $content = $request->toArray();
        $capitalId = $content['capitalId'] ?? 0;
        $country->setCapital($cityRepository->find($capitalId));
        $errors = $validator->validate($country);
        if($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);    
        }
        $entityManager->persist($country);
        $entityManager->flush();
        $context = SerializationContext::create()->setGroups(["getCountry"]);
        $jsonCountry = $serializer->serialize($country, 'json', $context);
        $location = $urlGeneratorInterface->generate('countries.get', ['idCountry' => $country->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonCountry . ', {"message":"For the creation to be complete please create a city and inform it is the capital of its country."}', Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Path that updates a country then returns it
     * 
     * @param Request $request
     * @param Country $country
     * @param CityRepository $cityRepository
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGeneratorInterface
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/countries/{idCountry}', name: 'countries.update', methods:['PATCH'])]
    #[ParamConverter('country', options: ['id' => 'idCountry'])]
    public function updateCountry(Request $request, Country $country, CityRepository $cityRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGeneratorInterface, ValidatorInterface $validator): JsonResponse
    {
        if(!$country->isStatus()){
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, []);
        }

        $updateCountry = $serializer->deserialize(
            $request->getContent(),
            Country::class,
            'json',
        );
        $country->setStatus(true);
        $content = $request->toArray();
        $country->setName($updateCountry->getName() ?? $country->getName());
        $capitalId = $content['capitalId'] ?? $country->getCapital()->getId();
        $country->setCapital($cityRepository->find($capitalId));
        $country->setEuropean($updateCountry->isEuropean() ?? $country->isEuropean());
        $country->setLanguages($updateCountry->getLanguages() ?? $country->getLanguages());
        $errors = $validator->validate($country);
        if($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);    
        }
        $entityManager->persist($country);
        $entityManager->flush();
        $context = SerializationContext::create()->setGroups(["getCountry"]);
        $jsonCountry = $serializer->serialize($country, 'json', $context);
        $location = $urlGeneratorInterface->generate('countries.get', ['idCountry' => $country->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonCountry, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Methods that deletes a country (no path)
     * 
     * @param Country $country
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[ParamConverter('country', options: ['id' => 'idCountry'])]
    public function deleteCountry(Country $country, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($country);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
    }

    /**
     * Path that deactivates a country
     * 
     * @param Country $country
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[Route('/api/countries/{idCountry}', name: 'countries.delete', methods:['DELETE'])]
    #[ParamConverter('country', options: ['id' => 'idCountry'])]
    public function deactivateCountry(Country $country, EntityManagerInterface $entityManager): JsonResponse
    {
        $country->setStatus(false);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
    }
}
