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

	function __construct(\Swift_Mailer $mailer, EngineInterface $templating,
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
		$url       = $this->router->generate('homepage', array(), true);
		$templates = $this->getEmailTemplates($user, 'user-created', $url);
		$this->sendMail(array(
			'subject' => 'Votre compte utilisateur sur ' . $this->config['websiteName'] . ' !',
			'from'    => $this->config['from'],
			'to'      => $user->getEmail(),
			'body'    => $templates['html'],
			'text'    => $templates['text'],
		));
	}

	public function postCreate(User $user)
	{
		$this->setNewSecurityHash($user);
		$url       = $this->router->generate('record_validing_email', array('hash' => $user->getSecurityHash()), true);
		$templates = $this->getEmailTemplates($user, 'post-record', $url);
		$this->sendMail(array(
			'subject' => 'Validation ',
			'from'    => $this->config['from'],
			'to'      => $user->getEmail(),
			'body'    => $templates['html'],
			'text'    => $templates['text'],
		));
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

		$templates = $this->getEmailTemplates($user, 'change-password');
		$this->sendMail(array(
			'subject' => "Modification de votre mot de passe.",
			'from'    => $this->config['from'],
			'to'      => $user->getEmail(),
			'body'    => $templates['html'],
			'text'    => $templates['text'],
		));
		$this->updateUser($user);
	}


	/**
	 * Envoi le mail via emailHandler
	 *
	 * @param array $options
	 */
	private function sendMail(array $options)
	{
		$EmailHandler = new EmailHandler($this->mailer, $this->templating);
		$EmailHandler->sendMail($options);
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
		$templates = $this->getEmailTemplates($user, 'change-password');
		$this->sendMail(array(
			'subject' => "Réinitialisation de votre mot de passe.",
			'from'    => $this->config['from'],
			'to'      => $user->getEmail(),
			'body'    => $templates['html'],
			'text'    => $templates['text'],
		));
	}


	/**
	 * erreur de changement du mot de passe
	 *
	 * @param User $user
	 */
	public function changePasswordError(User $user)
	{
		$templates = $this->getEmailTemplates($user, 'change-password-error');
		$this->sendMail(array(
			'subject' => "Echec de la modification de votre mot de passe.",
			'from'    => $this->config['from'],
			'to'      => $user->getEmail(),
			'body'    => $templates['html'],
			'text'    => $templates['text'],
		));
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
		$newEmail = $user->getEmailTmp();
		$this->setNewSecurityHash($user);
		$url       = $this->router->generate('rudakUser_email_change_confirmation', array('hash' => $user->getSecurityHash(),), true);
		$templates = $this->getEmailTemplates($user, 'change-email-request', $url);
		$this->sendMail(array(
			'subject' => 'Demande de changement d\'adresse email',
			'from'    => $this->config['from'],
			'to'      => $newEmail,
			'body'    => $templates['html'],
			'text'    => $templates['text'],
		));
		$this->updateUser($user);
	}

	public function changeEmailSuccess(User $user)
	{
		$newEmail  = $user->getEmailTmp();
		$templates = $this->getEmailTemplates($user, 'change-email');
		$this->sendMail(array(
			'subject' => 'Changement d\'adresse email réussi',
			'from'    => $this->config['from'],
			'to'      => $newEmail,
			'body'    => $templates['html'],
			'text'    => $templates['text']
		));
		$user->setEmailTmp(null);
		$user->setEmail($newEmail);
		$this->eraseSecurityHash($user);

		$this->updateUser($user);
	}

	public function reinitPasswordRequest(User $user)
	{
		$this->setNewSecurityHash($user);
		$url       = $this->router->generate('rudakUser_reinit_mail_answer', array(
			'hash' => $user->getSecurityHash()
		), true);
		$templates = $this->getEmailTemplates($user, 'link-password-init', $url);

		$this->sendMail(array(
			'subject' => 'Mot de passe perdu',
			'from'    => $this->config['from'],
			'to'      => $user->getEmail(),
			'body'    => $templates['html'],
			'text'    => $templates['text'],
		));
		$session = new Session();
		$session->getFlashBag()->add('notice', 'Email de récupération envoyé, vous disposez d\'une heure pour changer votre mot de passe.');
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

	private function getEmailTemplates(User $user, $template, $link = null)
	{
		$date = new \Datetime('NOW');
		# TODO: basculer ca du coté emailHandler
		# TODO: voir les dates de validité, je crois que je me suis planté avec NOW...
		# TODO: envoyer mail apres avoir validé l'adresse post record


		return array(
			'html' => $this->templating->render('RudakUserBundle:Email:' . $template . '.html.twig', array(
				'user'    => $user,
				'date'    => $date,
				'website' => $this->config['websiteName'],
				'link'    => $link
			)),
			'text' => $this->templating->render('RudakUserBundle:Email:' . $template . '.txt.twig', array(
				'user'    => $user,
				'website' => $this->config['websiteName'],
				'link'    => $link,
				'date'    => $date,
			))
		);

	}
}