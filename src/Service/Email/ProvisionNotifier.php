<?php

namespace App\Service\Email;

use App\Entity\Provision;
use App\Service\Email\Mailer as EmailMailer;

class ProvisionNotifier
{
    private $mailer;

    public function __construct(EmailMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function notify(Provision $provision)
    {
        return $this->mailer->sendMessage(
            $provision->getSupplier()->getEmail(),
            "CAP MÉCHANT : Commande pour le " . ($provision->getProvisionDate()->format('d/m/Y')),
            "email/provision.html.twig",
            ['provision' => $provision]
        );
    }
}