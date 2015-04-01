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
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Templating\EngineInterface;

class UserHandler
{

	private   $mailer;

	private   $templating;

	private   $em;

	private   $encoder;

	private   $router;

	protected $container;

	private   $config;

	function __construct(\Swift_Mailer $mailer,
						 EngineInterface $templating,
						 EntityManager $entityManager,
						 EncoderFactoryInterface $encoder,
						 Router $router,
						 $config)
	{
		$this->mailer     = $mailer;
		$this->templating = $templating;
		$this->em         = $entityManager;
		$this->encoder    = $encoder;
		$this->router     = $router;
		$this->config     = $config;
	}

	/**
	 * Méthode appelée quand on change de mot de passe
	 *
	 * @param User $user
	 */
	public function changePasswordSuccessfull(User $user)
	{
		$user->setPassword($this->getEncodedPassword($user));

		$this->sendMail(array(
			'subject' => "Modification de votre mot de passe.",
			'from'    => $this->config['from'],
			'to'      => $user->getEmail(),
			'body'    => $this->templating->render('RudakUserBundle:Email:change-password.html.twig', array(
				'user' => $user,
				'date' => new \Datetime('NOW'),
			))
		));
		$this->em->persist($user);
		$this->em->flush();
	}


	/**
	 * Retourne le mot de passe encodé
	 *
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
	 *
	 * @param       $user
	 * @param array $options
	 */
	private function sendMail(array $options)
	{
		$message = \Swift_Message::newInstance()
								 ->setSubject($options['subject'])
								 ->setFrom($options['from'])
								 ->setContentType("text/html")
								 ->setTo($options['to'])
								 ->setBody($options['body']);
		// envoi
		$this->mailer->send($message);
	}

	/**
	 * Intervient lors du success de la réinitialisation du mot de passe
	 *
	 * @param User $user
	 */
	public function reinitPasswordSuccess(User $user)
	{
		$user->setSecurityHash(null);
		$user->setSecurityHashExpireAt(null);
		$user->setEmailValidation(new \Datetime('NOW'));
		$user->setIsActive(true);
		$user->setPassword($this->getEncodedPassword($user));
		$user->setPlainPassword(null);
		$this->sendMail(array(
			'subject' => "Réinitialisation de votre mot de passe.",
			'from'    => $this->config['from'],
			'to'      => $user->getEmail(),
			'body'    => $this->templating->render('RudakUserBundle:Email:change-password.html.twig', array(
				'user' => $user,
				'date' => new \Datetime('NOW'),
			))
		));
	}


	public function changePasswordError(User $user)
	{
		$this->sendMail(array(
			'subject' => "Echec de la modification de votre mot de passe.",
			'from'    => $this->config['from'],
			'to'      => $user->getEmail(),
			'body'    => $this->templating->render('RudakUserBundle:Email:change-password-error.html.twig', array(
				'user' => $user,
				'date' => new \Datetime('NOW'),
			))
		));
	}

	public function emailValidationSuccess(User $user)
	{
		$user->setSecurityHash(null);
		$user->setIsActive(true);
		$user->setEmailValidation(new \Datetime('NOW'));
	}

	public function changeEmailRequest(User $user)
	{
		$newEmail = $user->getEmailTmp();
		$user->setSecurityHash(md5(uniqid()));
		$user->setSecurityHashExpireAt(new \DateTime('+1 hour'));
		$this->sendMail(array(
			'subject' => 'Demande de changement d\'adresse email',
			'from'    => $this->config['from'],
			'to'      => $newEmail,
			'body'    => $this->templating->render('RudakUserBundle:Email:change-email-request.html.twig', array(
				'user' => $user,
				'link' => $this->router->generate('rudakUser_email_change_confirmation', array(
					'hash' => $user->getSecurityHash(),
				), true),
				'date' => new \Datetime('NOW'),
			)),
		));
		$this->em->persist($user);
		$this->em->flush();
	}

	public function changeEmailSuccess(User $user)
	{
		$newEmail = $user->getEmailTmp();
		$this->sendMail(array(
			'subject' => 'Changement d\'adresse email effectuée',
			'from'    => $this->config['from'],
			'to'      => $newEmail,
			'body'    => $this->templating->render('RudakUserBundle:Email:change-email.html.twig', array(
				'user' => $user,
				'date' => new \Datetime('NOW'),
			)),
		));
		$user->setEmailTmp(null);
		$user->setEmail($newEmail);
		$user->setSecurityHash(null);
		$user->setSecurityHashExpireAt(null);

		$this->em->persist($user);
		$this->em->flush();
	}

	public function reinitPasswordRequest()
	{

	}

	public function autoGenPasswordRequest()
	{

	}
}