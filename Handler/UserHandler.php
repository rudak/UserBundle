<?php
/**
 * Created by PhpStorm.
 * User: rudak
 * Date: 18/03/2015
 * Time: 19:34
 */

namespace Rudak\UserBundle\Handler;


use Doctrine\ORM\EntityManager;
use Rudak\UserBundle\Entity\User;
use Rudak\UserBundle\Form\Model\ChangePassword;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Templating\EngineInterface;

class UserHandler
{
	private $mailer;
	private $templating;
	private $em;
	private $encoder;

	function __construct(\Swift_Mailer $mailer, EngineInterface $templating, EntityManager $entityManager, EncoderFactoryInterface $encoder)
	{
		$this->mailer     = $mailer;
		$this->templating = $templating;
		$this->em         = $entityManager;
		$this->encoder    = $encoder;
	}

	/**
	 * Méthode appelée quand on change de mot de passe
	 * @param User $user
	 */
	public function changePasswordSuccessfull(User $user)
	{
		$user->setPassword($this->getEncodedPassword($user));

		$this->sendMail($user, array(
			'subject' => "Modification de votre mot de passe.",
			'from' => 'admin@votresite.com',
			'body' => $this->templating->render('RudakUserBundle:Email:change-password.html.twig', array(
				'user' => $user,
				'date' => new \Datetime('NOW'),
			))
		));
		$this->em->persist($user);
		$this->em->flush();
	}


	/**
	 * Retourne le mot de passe encodé
	 * @param $user
	 * @return string
	 */
	private function getEncodedPassword(User $user)
	{
		$encoder = $this->encoder->getEncoder($user);
		return $encoder->encodePassword($user->getPlainPassword(), $user->getSalt());
	}


	/**
	 * Envoi le mail pour prevenir du changement de mot de passe
	 * @param $user
	 * @param array $options
	 */
	private function sendMail(User $user, array $options)
	{
		$message = \Swift_Message::newInstance()
								 ->setSubject($options['subject'])
								 ->setFrom($options['from'])
								 ->setContentType("text/html")
								 ->setTo($user->getEmail())
								 ->setBody($options['body']);
		// envoi
		$this->mailer->send($message);
	}

	/**
	 * Intervient lors du success de la réinitialisation du mot de passe
	 * @param User $user
	 */
	public function reinitPasswordSuccess(User $user)
	{
		$user->setRecoveryHash(null);
		$user->setRecoveryExpireAt(null);
		$user->setEmailValidation(new \Datetime('NOW'));
		$user->setIsActive(true);
		$user->setPassword($this->getEncodedPassword($user));
		$user->setPlainPassword(null);
		$this->sendMail($user, array(
			'subject' => "Réinitialisation de votre mot de passe.",
			'from' => 'admin@votresite.com',
			'body' => $this->templating->render('RudakUserBundle:Email:change-password.html.twig', array(
				'user' => $user,
				'date' => new \Datetime('NOW'),
			))
		));
	}


	public function changePasswordError(User $user)
	{
		$this->sendMail($user, array(
			'subject' => "Echec de la modification de votre mot de passe.",
			'from' => 'admin@votresite.com',
			'body' => $this->templating->render('RudakUserBundle:Email:change-password-error.html.twig', array(
				'user' => $user,
				'date' => new \Datetime('NOW'),
			))
		));
	}

}