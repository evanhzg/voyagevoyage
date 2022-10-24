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
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/CountryController.php',
        ]);
    }

    /**
     * Get a response containing every country in the database
     * 
     * 
     * @param Country $country
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route("/api/countries", name: "country.getAll")]
    public function getAllPlaces(CountryRepository $repository, SerializerInterface $serializerInterface): JsonResponse
    {
        $countries = $repository->findBy(['status' => 'true']);
        $jsonCountries = $serializerInterface->serialize($countries, 'json', ["groups" => 'getAllCountries']);
        return new JsonResponse($jsonCountries, Response::HTTP_OK,[], false);
    }

    /**
     * Get a country depending of the given id
     * 
     * 
     * @param Country $country
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route("/api/countries/{idCountry}", name: "country.get", methods: ['GET'])]
    #[ParamConverter("country", options: ["id" =>"idCountry"])]
    public function getCountry(CountryRepository $repository, Country $country, SerializerInterface $serializer): JsonResponse
    {
        $jsonCountry = $serializer->serialize($country, 'json', ["groups" => 'getCountry']);
        
        return $country->isStatus() ? 
        new JsonResponse($jsonCountry, Response::HTTP_OK, ['accept' => 'jsons'], true) : 
        new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }

    /**
     * Deletes a given country
     * 
     * @param Country $country
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[Route("/api/countries/{idCountry}", name: "country.delete", methods: ['DELETE'])]
    #[ParamConverter("country", options: ["id" =>"idCountry"])]
    public function deleteCountry(Country $country, EntityManagerInterface $entityManager): JsonResponse
    {
        $country->setStatus(false);
        $entityManager->flush();

        return new JsonResponse();
    }

    /**
     * Add a new Country
     * 
     * @param int $id
     * @param CountryRepository $repository
     * @return JsonResponse
     */
    #[Route("/api/country", name: "country.create", methods: ['POST'])]
    public function createCountry(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $country = $serializer->deserialize(
            $request->getContent(),
            Country::class,
            'json'
        );

        $country->setStatus(true);

        $entityManager->persist($country);
        $entityManager->flush();

        $jsonCountry = $serializer->serialize($country, 'json', ['groups' => 'getCountry']);

        return new JsonResponse($jsonCountry, Response::HTTP_CREATED, [], true);
    }
}
