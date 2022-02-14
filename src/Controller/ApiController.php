<?php

/**
 * AppiController
 *
 * Informations :
 * Default app controller
 *
 * @author Sébastien : sebastien.maillot@coding-academy.fr
 */
namespace App\Controller;

use App\Repository\ProvisionRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController extends AbstractController
{
    /**
     * @Route("/", name="api")
     * 
     * Informations :
     * Website entrypoint
     */
    public function index(): RedirectResponse
    {
        // return $this->render('base.html.twig');
        return $this->redirectToRoute('api_entrypoint');
    }

    /**
     * @Route("/api/provisions/intern", name="provisions_add_intern_value", methods={"GET"})
     * 
     * Informations :
     * Set to database the last suppliers exported from VIF
     */
    public function updateProvisions(ProvisionRepository $provisionRepository): JsonResponse
    {
        try {
            $provisions = $provisionRepository->findAll();
            foreach ($provisions as $provision) {
                $isIntern = $provision->getSupplier()->getIsIntern();
                $provision->setIsIntern($isIntern);
            }
            $this->getDoctrine()->getManager()->flush();
            return new JsonResponse(["data" => "success"]);
        } catch (\Exception $e) {
            return new JsonResponse($e);
        }

    }
}
