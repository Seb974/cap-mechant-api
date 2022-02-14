<?php

namespace App\Service\Email;

class ImportNotifier
{
    private $sender;
    private $mailer;
    private $appName;
    private $receptor;

    public function __construct($sender, $appName, $receptor, \Swift_Mailer $mailer)
    {
        $this->sender       = $sender;
        $this->mailer       = $mailer;
        $this->appName      = $appName;
        $this->receptor     = $receptor;
    }

    public function notify($status)
    {
        $body = $status !== 0 ? 
            "Echec de l'importation des données. Veuillez s'il vous plaît faire l'importation manuellement." :
            "Les données de VIF ont été importées avec succès.";

        try {
            $message = new \Swift_Message();
            $message->setSubject('Importation du ' . (new \DateTime())->format('d/m/Y'))
                    ->setFrom([$this->sender => $this->appName])
                    ->setTo([$this->receptor])
                    ->setBody($body);
            $return = $this->mailer->send($message);
            $emailStatus = $return !== false && $return > 0 ? 'done' : 'failed';
        } catch (\Exception $e) {
            $emailStatus = 'failed';
        } finally {
            return $emailStatus;
        }
    }
}