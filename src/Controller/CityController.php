<?php

namespace App\Controller;

use App\Entities\City;
use App\Repository\CityRepository;
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

    #[Route("/api/cities", name: "city.getAll")]
    public function getAllPlaces(CityRepository $repository, SerializerInterface $serializerInterface): JsonResponse
    {
        $cities = $repository->findAll();
        $jsonCities = $serializerInterface->serialize($cities, 'json', ["groups" => 'getAllCities']);
        return new JsonResponse($jsonCities, Response::HTTP_OK,[], false);
    }
}
