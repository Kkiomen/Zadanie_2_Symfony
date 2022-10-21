<?php

namespace App\Controller;


use App\Service\CityService;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    private CityService $cityService;

    public function __construct(CityService $cityService)
    {
        $this->cityService = $cityService;
    }


    /**
     * @Route("/",
     *     name="index")
     * @Template("cityList.html.twig")
     */
    public function indexPage(PaginatorInterface $paginator, Request $request)
    {
        $cities = $this->cityService->getAllCities();

        $pagination = $paginator->paginate(
            $cities,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 5)
        );

        return [
            'title' => 'Lista miast',
            'pagination' => $pagination
        ];
    }

}