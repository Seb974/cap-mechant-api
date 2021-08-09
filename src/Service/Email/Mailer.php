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

    public function sendMessage(string $sendTo, string $subject, string $template, array $args)
    {
        try {
            $status = '';
            $message = new \Swift_Message();
            $message->setSubject($subject)
                    ->setFrom($this->sender)
                    ->setTo($sendTo)
                    ->setBody($this->templating->render($template, $args), 'text/html');
        } catch (\Exception $e) {
            $status = 'failed';
        } finally {
            return $status !== 'failed' ? $this->mailer->send($message) : '';
        }
    }
}