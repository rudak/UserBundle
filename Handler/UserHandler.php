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
	 * @param ChangePassword $changePassword
	 */
	public function changeSuccessfull(User $user, ChangePassword $changePassword)
	{
		$newPassword = $this->getEncodedPassword($user, $changePassword->getNewPassword());
		$user->setPassword($newPassword);

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
	 * @param $plainPassword
	 * @return string
	 */
	private function getEncodedPassword($user, $plainPassword)
	{
		$encoder = $this->encoder->getEncoder($user);
		return $encoder->encodePassword($plainPassword, $user->getSalt());
	}

	/**
	 * Envoi le mail pour prevenir du changement de mot de passe
	 * @param $user
	 * @param array $options
	 */
	private function sendMail($user, array $options)
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

}