<?php

namespace App\Controller;

use App\Entity\Country;
use App\Repository\CountryRepository;
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
class CountryController extends AbstractController
{
    #[Route('/country', name: 'app_country')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Wrong route, try "api/country"',
        ]);
    }

    /**
     * Path that returns all coutries
     * 
     * @param CountryRepository $countryRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/country', name: 'countries.getAll', methods: ['GET'])]
    public function getAllCountries(CountryRepository $countryRepository, SerializerInterface $serializer): JsonResponse
    {
        $countries = $countryRepository->findAll();
        $jsonCountries = $serializer->serialize($countries, 'json', ["groups" => "getAllCountries"]);
        return new JsonResponse($jsonCountries, Response::HTTP_OK, [], true);
    }

    /**
     * Path that returns one country by its id
     * 
     * @param Country $country
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/country/{idCountry}', name: 'country.get', methods:['GET'])]
    #[ParamConverter('country', options: ['id' => 'idCountry'])]
    public function getCountry(Country $country, SerializerInterface $serializer): JsonResponse
    {
        $country->getCapital();
        $jsonCountry = $serializer->serialize($country, 'json', ["groups" => "getCountry"]);
        return new JsonResponse($jsonCountry, Response::HTTP_OK, ['accept' => 'jsons'], true);
    }

    /**
     * Path that returns all coutries
     * 
     * @param CountryRepository $countryRepository
     * @param SerializerInterface $serializer
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
     * Path that returns all coutries
     * 
     * @param CountryRepository $countryRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/country/{idCountry}', name: 'countries.delete', methods:['DELETE'])]
    #[ParamConverter('country', options: ['id' => 'idCountry'])]
    public function desactivateCountry(Country $country, EntityManagerInterface $entityManager): JsonResponse
    {
        $country->setStatus(false);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
    }

    /**
     * Path that returns all coutries
     * 
     * @param CountryRepository $countryRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/country', name: 'countries.create', methods:['POST'])]
    public function createCountry(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGeneratorInterface, ValidatorInterface $validator): JsonResponse
    {
        $country = $serializer->deserialize(
            $request->getContent(),
            Country::class,
            'json'
        );
        $country->setStatus(true);
        $errors = $validator->validate($country);
        if($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);    
        }
        $entityManager->persist($country);
        $entityManager->flush();
        $jsonCountry = $serializer->serialize($country, 'json', ['groups' => "getCountry"]);
        $location = $urlGeneratorInterface->generate('country.get', ['idCountry' => $country->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonCountry, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Path that returns all coutries
     * 
     * @param CountryRepository $countryRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/country/{idCountry}', name: 'country.update', methods:['PATCH'])]
    #[ParamConverter('country', options: ['id' => 'idCountry'])]
    public function updateCountry(Request $request, Country $country, EntityManagerInterface $entityManager, SerializerInterface $serializerInterface, UrlGeneratorInterface $urlGeneratorInterface): JsonResponse
    {
        $updateCountry = $serializerInterface->deserialize(
            $request->getContent(),
            Country::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $country] 
        );
        $updateCountry->setStatus(true);
        $entityManager->persist($country);
        $entityManager->flush();
        $jsonCountry = $serializerInterface->serialize($updateCountry, 'json', ['groups' => "getCountry"]);
        $location = $urlGeneratorInterface->generate('country.get', ['idCountry' => $updateCountry->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonCountry, Response::HTTP_CREATED, ["Location" => $location], true);
    }
}
