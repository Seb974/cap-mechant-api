<?php

namespace App\Service\Email;

class Mailer
{
    private $sender;
    private $mailer;
    private $templating;

    public function __construct($sender, \Swift_Mailer $mailer, \Twig\Environment $templating)
    {
        $this->sender       = $sender;
        $this->mailer       = $mailer;
        $this->templating   = $templating;
    }

    public function sendMessage($sendTo, string $subject, string $template, array $args)
    {
        try {
            $status = 'done';
            // throw new \Exception("L'envoi d'email a merdé");
            $message = new \Swift_Message();
            $message->setSubject($subject)
                    ->setFrom($this->sender)
                    ->setTo($sendTo)
                    ->setBody($this->templating->render($template, $args), 'text/html');
            $this->mailer->send($message);
        } catch (\Exception $e) {
            $status = 'failed';
        } finally {
            return $status;
        }
    }
}