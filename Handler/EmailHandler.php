<?php
/**
 * Created by PhpStorm.
 * User: rudak
 * Date: 09/04/2015
 * Time: 12:46
 */

namespace Rudak\UserBundle\Handler;


use Rudak\UserBundle\Entity\User;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\Templating\EngineInterface;

class EmailHandler
{

	private $mailer;

	private $templating;

	private $user;

	private $options;

	function __construct(Swift_Mailer $mailer, EngineInterface $templating, User $user, array $options)
	{
		$this->mailer     = $mailer;
		$this->templating = $templating;
		$this->user       = $user;
		$this->options    = $options;
	}


	public function sendMail()
	{
		$templates = $this->getEmailTemplates();
		$message   = Swift_Message::newInstance()
								  ->setSubject($this->getOption('subject'))
								  ->setFrom($this->getOption('from'))
								  ->setContentType("text/html")
								  ->setTo($this->getOption('to'))
								  ->addPart($templates['text'], 'text/plain')
								  ->setBody($templates['html'], 'text/html');
		$this->mailer->send($message);
	}


	private function getEmailTemplates()
	{
		$date = new \Datetime('NOW');
		# TODO: voir les dates de validité, je crois que je me suis planté avec NOW...
		# TODO: envoyer mail apres avoir validé l'adresse post record

		return array(
			'html' => $this->templating->render('RudakUserBundle:Email:' . $this->getOption('template') . '.html.twig', array(
				'user'    => $this->user,
				'date'    => $date,
				'website' => $this->getOption('website_name'),
				'link'    => $this->getOption('link')
			)),
			'text' => $this->templating->render('RudakUserBundle:Email:' . $this->getOption('template') . '.txt.twig', array(
				'user'    => $this->user,
				'website' => $this->getOption('website_name'),
				'link'    => $this->getOption('link'),
				'date'    => $date,
			))
		);
	}

	private function getOption($index)
	{
		if (!array_key_exists($index, $this->options)) {

			return null;
		}

		return $this->options[$index];
	}
}