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
use Swift_Mailer;
use Symfony\Component\HttpFoundation\Session\Session;
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

	function __construct(Swift_Mailer $mailer, EngineInterface $templating,
						 EntityManager $entityManager, EncoderFactoryInterface $encoder,
						 Router $router, $config)
	{
		$this->mailer     = $mailer;
		$this->templating = $templating;
		$this->em         = $entityManager;
		$this->encoder    = $encoder;
		$this->router     = $router;
		$this->config     = $config;
	}

	public function userCreated(User $user)
	{
		$options      = array(
			'subject'      => 'Votre compte utilisateur sur ' . $this->config['websiteName'] . ' !',
			'from'         => $this->config['from'],
			'to'           => $user->getEmail(),
			'template'     => 'user-created',
			'website_name' => $this->config['websiteName'],
			'link'         => $this->router->generate('homepage', array(), true),
		);
		$EmailHandler = new EmailHandler($this->mailer, $this->templating, $user, $options);
		$EmailHandler->sendMail();
	}

	public function postCreate(User $user)
	{
		$this->setNewSecurityHash($user);
		$options      = array(
			'subject'      => 'Validation',
			'from'         => $this->config['from'],
			'to'           => $user->getEmail(),
			'template'     => 'post-record',
			'website_name' => $this->config['websiteName'],
			'link'         => $this->router->generate('record_validing_email', array('hash' => $user->getSecurityHash()), true),
		);
		$EmailHandler = new EmailHandler($this->mailer, $this->templating, $user, $options);
		$EmailHandler->sendMail();
		$this->updateUser($user);
	}

	/**
	 * Méthode appelée quand on change de mot de passe
	 *
	 * @param User $user
	 */
	public function changePasswordSuccessfull(User $user)
	{
		$user->setPassword($this->getEncodedPassword($user));
		$options      = array(
			'subject'      => "Modification de votre mot de passe.",
			'from'         => $this->config['from'],
			'to'           => $user->getEmail(),
			'template'     => 'change-password',
			'website_name' => $this->config['websiteName']
		);
		$EmailHandler = new EmailHandler($this->mailer, $this->templating, $user, $options);
		$EmailHandler->sendMail();
		$this->updateUser($user);
	}

	/**
	 * erreur de changement du mot de passe
	 *
	 * @param User $user
	 */
	public function changePasswordError(User $user)
	{
		$options      = array(
			'subject'      => "Echec de la modification de votre mot de passe.",
			'from'         => $this->config['from'],
			'to'           => $user->getEmail(),
			'template'     => 'change-password-error',
			'website_name' => $this->config['websiteName'],
		);
		$EmailHandler = new EmailHandler($this->mailer, $this->templating, $user, $options);
		$EmailHandler->sendMail();
	}

	public function reinitPasswordRequest(User $user)
	{
		$this->setNewSecurityHash($user);
		$options      = array(
			'subject'      => 'Mot de passe perdu',
			'from'         => $this->config['from'],
			'to'           => $user->getEmail(),
			'template'     => 'link-password-init',
			'website_name' => $this->config['websiteName'],
			'link'         => $this->router->generate('rudakUser_reinit_mail_answer', array('hash' => $user->getSecurityHash()), true),
		);
		$EmailHandler = new EmailHandler($this->mailer, $this->templating, $user, $options);
		$EmailHandler->sendMail();

		$session = new Session();
		$session->getFlashBag()->add('notice', 'Email de récupération envoyé, vous disposez d\'une heure pour changer votre mot de passe.');
	}


	/**
	 * Intervient lors du success de la réinitialisation du mot de passe
	 *
	 * @param User $user
	 */
	public function reinitPasswordSuccess(User $user)
	{
		$this->eraseSecurityHash($user);
		$user->setEmailValidation(new \Datetime('NOW'));
		$user->setIsActive(true);
		$user->setPassword($this->getEncodedPassword($user));
		$user->setPlainPassword(null);

		$options      = array(
			'subject'      => "Réinitialisation de votre mot de passe.",
			'from'         => $this->config['from'],
			'to'           => $user->getEmail(),
			'template'     => 'change-password',
			'website_name' => $this->config['websiteName'],
		);
		$EmailHandler = new EmailHandler($this->mailer, $this->templating, $user, $options);
		$EmailHandler->sendMail();
	}


	/**
	 * Réussite de la validation du mail
	 *
	 * @param User $user
	 */
	public function emailValidationSuccess(User $user)
	{
		$this->eraseSecurityHash($user);
		$user->setIsActive(true);
		$user->setEmailValidation(new \Datetime('NOW'));
	}

	public function changeEmailRequest(User $user)
	{
		$this->setNewSecurityHash($user);

		$options = array(
			'subject'      => 'Changement d\'adresse e-mail',
			'from'         => $this->config['from'],
			'to'           => $user->getEmailTmp(),
			'template'     => 'change-email-request',
			'website_name' => $this->config['websiteName'],
			'link'         => $this->router->generate('rudakUser_email_change_confirmation', array('hash' => $user->getSecurityHash()), true),
		);

		$EmailHandler = new EmailHandler($this->mailer, $this->templating, $user, $options);
		$EmailHandler->sendMail();
		$this->updateUser($user);
	}

	public function changeEmailSuccess(User $user)
	{
		$newEmail     = $user->getEmailTmp();
		$options      = array(
			'subject'      => 'Changement d\'adresse email réussi',
			'from'         => $this->config['from'],
			'to'           => $newEmail,
			'template'     => 'change-email',
			'website_name' => $this->config['websiteName']
		);
		$EmailHandler = new EmailHandler($this->mailer, $this->templating, $user, $options);
		$EmailHandler->sendMail();

		$user->setEmailTmp(null);
		$user->setEmail($newEmail);
		$this->eraseSecurityHash($user);

		$this->updateUser($user);
	}


	private function setNewSecurityHash(User $user)
	{
		$user->setSecurityHash(sha1(md5(uniqid(null, true))));
		$user->setSecurityHashExpireAt(new \Datetime('+1 hour'));
	}

	private function eraseSecurityHash(User $user)
	{
		$user->setSecurityHash(null);
		$user->setSecurityHashExpireAt(null);
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

	private function updateUser(User $user)
	{
		$this->em->persist($user);
		$this->em->flush();
	}
}