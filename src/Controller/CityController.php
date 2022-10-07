<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\City;
use App\Repository\CityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Faker\Generator;
use Faker\Factory;
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


    #[Route('/city', name: 'city.get', methode: ['POST'])]
    public function createCity(Request $request, EntityManager $entityManager) : JsonResponse
    {

    }
}
