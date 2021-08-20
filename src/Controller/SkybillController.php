<?php

namespace App\Controller;

use App\Entity\OrderEntity;
use App\Service\Chronopost\Chronopost;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * SkybillController
 *
 * Informations :
 * Contains actions to obtain printable label for orders destinated to be exported.
 *
 * @author Sébastien : sebastien.maillot@coding-academy.fr
 * @IsGranted("ROLE_PICKER")
 */
class SkybillController extends AbstractController
{
    /*
     * @Route("/api/skybills/{id}", name="skybill-create", methods={"POST"})
     *
     * Informations :
     * Create the skybill in ZPL encoded format
     */
    // public function create(OrderEntity $order, Chronopost $chronopost): JsonResponse
    // {
    //     // $skybill = $chronopost->getSkybill($order->getReservationNumber());
    //     return new JsonResponse(mb_convert_encoding($skybill, 'UTF-8', 'UTF-8'));
    // }
}