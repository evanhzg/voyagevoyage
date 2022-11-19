<?php

namespace App\Controller;

use App\Entity\Place;
use App\Repository\CityRepository;
use App\Repository\PlaceRepository;
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

class PlaceController extends AbstractController
{
    #[Route('/place', name: 'app_place')]
    public function index(Request $request): JsonResponse
    {
        return $this->json([
            'message' => "Wrong route, try 'http://" . explode('/', $request->getUri())[2] . "/api/places'",
        ]);
    }

    /**
     * Path that returns all places
     * 
     * @param Request $request
     * @param PlaceRepository $placeRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/places', name: 'places.getAll', methods: ['GET'])]
    public function getAllPlaces(Request $request, PlaceRepository $placeRepository, SerializerInterface $serializer): JsonResponse
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
        if($request->get('name') !== null) {
            $filters["name"] = " LIKE '%" . $request->get('name') . "%'";
        }
        if($request->get('type') !== null) {
            $filters["type"] = " LIKE '%" . $request->get('type') . "%'";
        }
        if($request->get('openDay') !== null) {
            $filters["open_days"] = " LIKE '%" . $request->get('openDay') . "%'";
        }
        if($request->get('cityId')){
            $filters["city"] = " = " . $request->get('cityId');
        }
        $places = $placeRepository->findWithPagination($page, $limit, $orderBy, $orderByDirection, $filters);
        $context = SerializationContext::create()->setGroups(["getAllPlaces"])->setSerializeNull(true);
        $jsonPlaces = $serializer->serialize($places, 'json', $context);
        return new JsonResponse($jsonPlaces, Response::HTTP_OK, [], true);
    }

    /**
     * Path that returns one place by its id
     * 
     * @param Place $place
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/places/{idPlace}', name: 'places.get', methods:['GET'])]
    #[ParamConverter('place', options: ['id' => 'idPlace'])]
    public function getPlace(Place $place, SerializerInterface $serializer): JsonResponse
    {
        if(!$place->isStatus()){
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, []);
        }
        $context = SerializationContext::create()->setGroups(["getPlace"])->setSerializeNull(true);
        $jsonPlace = $serializer->serialize($place, 'json', $context);
        return new JsonResponse($jsonPlace, Response::HTTP_OK, ['accept' => 'jsons'], true);
    }
    
    /**
     * Path that creates a place then returns it
     * 
     * @param Request $request
     * @param PlaceRepository $placeRepository
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGeneratorInterface
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/places', name: 'places.create', methods:['POST'])]
    public function createPlace(Request $request, CityRepository $cityRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGeneratorInterface, ValidatorInterface $validator): JsonResponse
    {
        $place = $serializer->deserialize(
            $request->getContent(),
            Place::class,
            'json'
        );
        $place->setStatus(true);
        $content = $request->toArray();
        $cityId = $content['cityId'] ?? 0;
        $place->setCity($cityRepository->find($cityId));
        $errors = $validator->validate($place);
        if($errors->count() > 0 || $place->getCity() == null){
            if($place->getCity() == null){
                return new JsonResponse($serializer->serialize($errors, 'json')  . ', {"property_path": "cityId", "message": "You must attach the place to a city."}', Response::HTTP_BAD_REQUEST, [], true);    
            } else {
                return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);    
            }
        }
        $entityManager->persist($place);
        $entityManager->flush();
        $context = SerializationContext::create()->setGroups(["getPlace"])->setSerializeNull(true);
        $jsonPlace = $serializer->serialize($place, 'json', $context);
        $location = $urlGeneratorInterface->generate('places.get', ['idPlace' => $place->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonPlace, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Path that updates a place then returns it
     * 
     * @param Request $request
     * @param Place $place
     * @param PlaceRepository $placeRepository
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGeneratorInterface
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/places/{idPlace}', name: 'places.update', methods:['PATCH'])]
    #[ParamConverter('place', options: ['id' => 'idPlace'])]
    public function updatePlace(Request $request, Place $place, CityRepository $cityRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGeneratorInterface, ValidatorInterface $validator): JsonResponse
    {
        if(!$place->isStatus()){
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, []);
        }

        $updatePlace = $serializer->deserialize(
            $request->getContent(),
            Place::class,
            'json',
        );
        $place->setStatus(true);
        $content = $request->toArray();
        $place->setName($updatePlace->getName() ?? $place->getName());
        $place->setType($updatePlace->getType() ?? $place->getType());
        $place->setAddress($updatePlace->getAddress() ?? $place->getAddress());
        $cityId = $content['cityId'] ?? $place->getCity()->getId();
        $place->setCity($cityRepository->find($cityId));
        $place->setOpenHour($updatePlace->getOpenHour() ?? $place->getOpenHour());
        $place->setClosedHour($updatePlace->getClosedHour() ?? $place->getClosedHour());
        $place->setOpenDays($updatePlace->getOpenDays() ?? $place->getOpenDays());
        $place->setPricing($updatePlace->getPricing() ?? $place->getPricing());
        $errors = $validator->validate($place);
        if($errors->count() > 0 || $place->getCity() == null){
            if($place->getCity() == null){
                return new JsonResponse($serializer->serialize($errors, 'json')  . ', {"property_path": "cityId", "message": "You must attach the place to a city."}', Response::HTTP_BAD_REQUEST, [], true);    
            } else {
                return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);    
            }
        }
        $entityManager->persist($place);
        $entityManager->flush();
        $context = SerializationContext::create()->setGroups(["getPlace"])->setSerializeNull(true);
        $jsonPlace = $serializer->serialize($place, 'json', $context);
        $location = $urlGeneratorInterface->generate('places.get', ['idPlace' => $place->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonPlace, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Methods that deletes a place (no path)
     * 
     * @param Place $place
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[ParamConverter('place', options: ['id' => 'idPlace'])]
    public function deletePlace(Place $place, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($place);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
    }

    /**
     * Path that deactivates a place
     * 
     * @param Place $place
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[Route('/api/places/{idPlace}', name: 'places.delete', methods:['DELETE'])]
    #[ParamConverter('place', options: ['id' => 'idPlace'])]
    public function deactivatePlace(Place $place, EntityManagerInterface $entityManager): JsonResponse
    {
        $place->setStatus(false);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
    }
}
