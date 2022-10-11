<?php

namespace App\Controller;

use Faker\Factory;
use Faker\Generator;
use App\Entity\Place;
use Doctrine\ORM\EntityManager;
use App\Repository\PlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class PlaceController extends AbstractController
{
    #[Route('/place', name: 'app_place')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PlaceController.php',
        ]);
    }

    /**
     * Get a response containing each place in the database
     * 
     * 
     * @param Country $country
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route("/api/places", name: "place.getAll")]
    public function getAllPlaces(PlaceRepository $repository, SerializerInterface $serializerInterface): JsonResponse
    {
        $places = $repository->findAll();
        $jsonPlaces = $serializerInterface->serialize($places, 'json', ["groups" => 'getAllPlaces']);
        return new JsonResponse($jsonPlaces, Response::HTTP_OK,[], false);
    }

    /**
     * Get a place based on the given id
     * 
     * 
     * @param Place $place
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route("/api/places/{idPlace}", name: "place.get", methods: ['GET'])]
    #[ParamConverter("place", options: ["id" =>"idPlace"])]
    public function getPlace(Place $place, SerializerInterface $serializer): JsonResponse
    {
        $jsonPlace = $serializer->serialize($place, 'json', ["groups" => 'getPlace']);
        
        return new JsonResponse($jsonPlace, Response::HTTP_OK, ['accept' => 'jsons'], true);
    }

    /**
     * Deleting a place name
     * 
     * 
     * @param Place $place
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */    
    #[Route('/api/place/{idPlace}', name: 'place.delete', methods: ['DELETE'])]
    #[ParamConverter("place", options : ["id"=>"idPlace"])]
    public function deleteCity(Place $place, EntityManagerInterface $entityManager) :JsonResponse
    {
        $place->setStatus(false);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);

    }

    /**
     * Adding a place name
     * 
     * 
     * @param Place $place
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/place/', name: 'place.turnOff', methods: ['POST'])]
    public function addplace(Request $request, EntityManager $entityManager, SerializerInterface $serializer):JsonResponse
    {
        $place = $serializer->deserialize($request->getContent(), Place::class, 'json');
        $place->setStatus(true);
        $entityManager->persist($place);
        $entityManager->flush();
        $jsonPlace = $serializer->serialize($place, 'json');
        return new JsonResponse($jsonPlace, Response::HTTP_CREATED, [], true);
    }

    /**
     * updating a place (changing adress, etc...)
     * 
     * 
     * @param Place $place
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/place/', name: 'place.update', methods: ['PUT'])]
    #[ParamConverter("placeName", options : ["id"=>"idPlace"])]
    public function updatePlace(Place $place, Request $request, EntityManager $entityManager, SerializerInterface $serializer):JsonResponse
    {
        $placeUpdate = $serializer->deserialize($request->getContent(), Place::class, 'json',
        [AbstractNormalizer::OBJECT_TO_POPULATE=> $place]);
        $request->toArray();   //i don't know if it's correct :)
        $placeUpdate->setStatus(true);
        $entityManager->persist($place);
        $entityManager->flush();
        $jsonPlace = $serializer->serialize($placeUpdate, 'json');
        return new JsonResponse($jsonPlace, Response::HTTP_RESET_CONTENT, [], true);
    }
}
