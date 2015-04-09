<?php
/**
 * Created by PhpStorm.
 * User: rudak
 * Date: 09/04/2015
 * Time: 12:46
 */

namespace Rudak\UserBundle\Handler;


use Swift_Mailer;
use Symfony\Component\Templating\EngineInterface;

class EmailHandler
{

	private $mailer;

	private $templating;

	function __construct(Swift_Mailer $mailer, EngineInterface $templating)
	{
		$this->mailer     = $mailer;
		$this->templating = $templating;
	}


	public function sendMail(array $options)
	{
		# TODO send text email too
		$message = \Swift_Message::newInstance()
								 ->setSubject($options['subject'])
								 ->setFrom($options['from'])
								 ->setContentType("text/html")
								 ->setTo($options['to'])
								 ->addPart($options['text'], 'text/plain')
								 ->setBody($options['body']);
		// envoi
		$this->mailer->send($message);
	}
}