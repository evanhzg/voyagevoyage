<?php

namespace App\Controller;

use App\Entity\Place;
use App\Repository\PlaceRepository;
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

    #[Route('/api/place/{id}', name: 'place.delete', methods: ['DELETE'])]
    #[ParamConverter("place", options : ["id"=>"idPlace"])]
    public function deleteCity(Place $place, EntityManagerInterface $entityManager) :JsonResponse
    {
         $place->setStatus(false);
         $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);

    }
}
