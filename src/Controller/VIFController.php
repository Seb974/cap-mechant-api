<?php

/**
 * AppiController
 *
 * Informations :
 * VIF controller, contains actions to exchange informations between VIF and the website
 *
 * @author Sébastien : sebastien.maillot@coding-academy.fr
 * 
 * @IsGranted("ROLE_TEAM")
 */
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\User\DataIntegrator as UserIntegrator;
use App\Service\Product\DataIntegrator as ProductIntegrator;
use App\Service\Supplier\DataIntegrator as SupplierIntegrator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VIFController extends AbstractController
{
    /**
     * @Route("/api/vif/products", name="vif-get-products", methods={"GET"})
     * 
     * Informations :
     * Set to database the last products exported from VIF
     */
    public function getProducts(ProductIntegrator $productIntegrator): JsonResponse
    {
        $status = $productIntegrator->editProducts();
        return new JsonResponse($status);
    }

    /**
     * @Route("/api/vif/users", name="vif-get-users", methods={"GET"})
     * 
     * Informations :
     * Set to database the last users exported from VIF
     */
    public function getUsers(UserIntegrator $userIntegrator): JsonResponse
    {
        $status = $userIntegrator->editUsers();
        return new JsonResponse($status);
    }

    /**
     * @Route("/api/vif/suppliers", name="vif-get-suppliers", methods={"GET"})
     * 
     * Informations :
     * Set to database the last suppliers exported from VIF
     */
    public function getSuppliers(SupplierIntegrator $supplierIntegrator): JsonResponse
    {
        $status = $supplierIntegrator->editSuppliers();
        return new JsonResponse($status);
    }
}