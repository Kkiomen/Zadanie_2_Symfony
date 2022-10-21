<?php

namespace App\Controller;

use App\Entity\City;
use App\Service\CityService;
use App\Service\NotificationService;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;

class CityController extends AbstractController
{

    private CityService $cityService;
    private NotificationService $notificationService;

    /**
     * @param CityService $cityService
     */
    public function __construct(CityService $cityService, NotificationService $notificationService)
    {
        $this->cityService = $cityService;
        $this->notificationService = $notificationService;
    }


    /**
     * @Route("/miasto/nowe",
     *     name="city_new")
     * @Template("form.html.twig")
     */
    public function cityForm(Request $request)
    {

        $form = $this->createFormBuilder()
            ->add('city', TextType::class, array(
                'label' => 'Miasto',
                'attr' => [
                    'placeholder' => 'Kraków'
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Dodaj miasto',
                'attr' => [
                    'class' => 'btn btn-info col-12 mt-3'
                ]
            ))
            ->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                $cityName = $form->get('city')->getData();
                if ($this->cityService->saveWithoutZipCode($cityName)) {
                    $this->notificationService->addSuccess('Poprawnie dodano miasto');
                    return $this->redirect($this->generateUrl('index'));
                } else {
                    $this->notificationService->addDanger('Nie udało się zapisać miasta');
                }

            } else {
                $this->notificationService->addDanger('Wystąpił błąd. Popraw podświetlone pola.');
            }
        }

        return [
            'title' => 'Dodawanie miasta',
            'titleForm' => 'Dodawanie miasta',
            'form' => $form->createView(),
        ];
    }


    /**
     * @Route("/miasto/pokaz/kod-pocztowy",
     *     name="city_with_zipcode")
     *
     * @Template("zipCodeList.html.twig")
     */
    public function zipCodeList(PaginatorInterface $paginator, Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('zipCode', TextType::class, array(
                'label' => 'Kod pocztowy',
                'attr' => [
                    'placeholder' => '11-111',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Szukaj',
                'attr' => [
                    'class' => 'btn btn-info col-12 mt-3'
                ]
            ))
            ->getForm();


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $zipCode = $form->get('zipCode')->getData();
            return $this->redirect($this->generateUrl('city_with_zipcode', ['value' => $zipCode]));
        }


        if ($request->query->get('value') !== null) {
            $cities = $this->cityService->getCitiesWhereLike($request->query->get('value'));
        } else {
            $cities = $this->cityService->getAllCities();
        }


        $pagination = $paginator->paginate(
            $cities,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 5)
        );

        return [
            'title' => 'Lista miast z kodem pocztowym',
            'pagination' => $pagination,
            'form' => $form->createView(),
        ];
    }


    /**
     * @Route("/aktualizacja/kod-pocztowy",
     *     name="update_zip_code")
     * @Template("form.html.twig")
     */
    public function zipCodeForm(Request $request)
    {

        $form = $this->createFormBuilder()
            ->add('city', EntityType::class, array(
                'label' => 'Miasto',
                'class' => City::class,
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'mb-3'
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ))
            ->add('zipCode', TextType::class, array(
                'label' => 'Kod pocztowy',
                'attr' => [
                    'placeholder' => '11-111',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex([
                        'pattern' => '/^[0-9]{2}-[0-9]{3}$/',
                        'message' => 'Kod pocztowy musi mieć format: 00-000'
                    ])
                ]
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Dodaj kod pocztowy',
                'attr' => [
                    'class' => 'btn btn-info col-12 mt-3'
                ]
            ))
            ->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                $cityName = $form->get('city')->getData();
                $zipCode = $form->get('zipCode')->getData();
                if ($this->cityService->updateZipCode($cityName, $zipCode)) {
                    $this->notificationService->addSuccess('Poprawnie dodano kod pocztowy');
                    return $this->redirect($this->generateUrl('city_with_zipcode'));
                }

            } else {
                $this->notificationService->addDanger('Wystąpił błąd. Popraw podświetlone pola.');
            }
        }

        return [
            'title' => 'Dodawanie kodu pocztowego',
            'titleForm' => 'Dodawanie kodu pocztowego',
            'form' => $form->createView(),
        ];
    }


    /**
     * @Route("/miasto/usun/{id}",
     *     name="city_delete")
     */
    public function deleteCity(Request $request, $id)
    {
        $this->cityService->deleteCity($id);
        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }

}