<?php

namespace Rudak\UserBundle\Controller;

use Rudak\UserBundle\Entity\User;
use Rudak\UserBundle\Event\ChangePasswordEvent;
use Rudak\UserBundle\Event\UserEvents;
use Rudak\UserBundle\Form\ChangePasswordType;
use Rudak\UserBundle\Form\LostPwdType;
use Rudak\UserBundle\Form\Model\ChangePassword;
use Rudak\UserBundle\Form\Model\RecoverPassword;
use Rudak\UserBundle\Form\RecoverPasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class PasswordController extends Controller
{
	public function lostAction()
	{
		return $this->render('RudakUserBundle:Password:lost.html.twig');
	}

	public function changePasswordAction(Request $request)
	{
		if (!$this->getUser()) {
			$this->addFlash('notice', 'Vous devez etre loggé pour modifier votre mot de passe.');
			return $this->redirectToRoute('homepage');
		}
		$changePassword = new ChangePassword();
		$form           = $this->createForm(new ChangePasswordType(), $changePassword, array(
			'action' => $this->generateUrl('rudakUser_pwd_modification')
		));

		$form->handleRequest($request);

		if ($form->isSubmitted()) {

			$user = $this->getUser();
			if ($form->isValid()) {
				$user->setPlainPassword($changePassword->getNewPassword());
				$changePasswordEvent = new ChangePasswordEvent($user);
				// evenement moddification de mot de passe
				$this
					->get('event_dispatcher')
					->dispatch(UserEvents::USER_PASSWORD_CHANGE_SUCCESS, $changePasswordEvent);
				$this->addFlash('notice', 'Mot de passe changé avec succès.');
				//return $this->redirect($this->generateUrl('rudakUser_profile'));
			}
			else {
				// formulaire invalide
				$changePasswordEvent = new ChangePasswordEvent($user);
				$this
					->get('event_dispatcher')
					->dispatch(UserEvents::USER_PASSWORD_CHANGE_ERROR, $changePasswordEvent);
			}
		}

		return $this->render('RudakUserBundle:Password:change.html.twig', array(
			'form' => $form->createView()
		));

	}

	/**
	 * Recherche un user selon l'info donnée (user ou email) et envoie un mail pour la recup
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function findUserAction(Request $request)
	{
		$data = $request->request->get('lostpwd_user_input');
		$em   = $this->getDoctrine()->getManager();
		$user = $em->getRepository('RudakUserBundle:User')->checkIfUserExists($data);

		if ($user && $user instanceof User) {
			$user->setRecoveryHash(sha1(md5(uniqid(null, true))));
			$user->setRecoveryExpireAt(new \Datetime('+1 hour'));
			$this->sendMail($user);
			$this->addFlash('notice', 'Email de récupération envoyé, vous disposez d\'une heure pour changer votre mot de passe.');
			$em->persist($user);
			$em->flush();
		}
		else {
			$this->addFlash('notice', 'Le nom d’utilisateur que vous avez entré ne correspond pas au nom enregistré sur nos serveurs pour votre compte.');
		}
		return $this->redirectToRoute('homepage');
	}


	/**
	 * Envoie un mail pour la recup du password
	 * @param User $user
	 */
	private function sendMail(User $user)
	{
		$message = \Swift_Message::newInstance()
								 ->setContentType("text/html")
								 ->setSubject('Mot de passe perdu')
								 ->setFrom('robot@mon-site.fr')
								 ->setTo($user->getEmail())
								 ->setBody($this->renderView('RudakUserBundle:Email:link-password-init.html.twig', array(
									 'user' => $user,
									 'link' => $this->generateUrl('rudakUser_reinit_mail_answer', array(
										 'hash' => $user->getRecoveryHash()
									 ), true)
								 )));
		$this->get('mailer')->send($message);
	}

	/**
	 * Traite la réponse du mail, et renvoie vers le formulaire de réinititlisation de mot de passe
	 * @param $hash
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function emailAnswerAction(Request $request, $hash)
	{
		$em   = $this->getDoctrine()->getManager();
		$user = $em->getRepository('RudakUserBundle:User')->getUserByHash($hash);

		if (!$user) {
			$this->addFlash('notice', 'Impossible de trouver une correspondance avec cette clé de réinitialisation.');
			return $this->redirectToRoute('homepage');
		}

		if (new \Datetime('NOW') > $user->getRecoveryExpireAt()) {
			$this->launchErrorEvent($user);
			$this->addFlash('notice', "Le code de réinitialisation est expiré, merci de recommencer la procédure.");
			return $this->redirectToRoute('rudakUser_lost_pwd');
		}
		// Récupération du formulaire
		$changePasswordModel = new RecoverPassword();
		$changePasswordModel->setHash($hash);
		$form = $this->getChangePasswordForm($changePasswordModel);

		$form->handleRequest($request);
		if ($form->isValid()) {
			$user->setPlainPassword($changePasswordModel->getNewPassword());
			$changePasswordEvent = new ChangePasswordEvent($user);
			$this
				->get('event_dispatcher')
				->dispatch(UserEvents::USER_PASSWORD_RECOVERED, $changePasswordEvent);

			$em->persist($user);
			$em->flush();

			$this->addFlash('notice', "Le mot de passe a été changé avec succès.");
			// si l'autologin est ok
			if (true) {
				//TODO : creer un argument de config
				$this->autoLogin($user, $request);
			}
			return $this->redirectToRoute('homepage');
		}
		return $this->render('RudakUserBundle:Password:init-form.html.twig', array(
			'form' => $form->createView(),
			'user' => $user
		));
	}

	/**
	 * Event correspondant a une erreur de reinitialisation de password
	 * @param $user
	 */
	private function launchErrorEvent($user)
	{
		// evenement changement de mot de passe foiré
		$passwordRecoveryErrorEvent = new PasswordRecoveryErrorEvent($user);
		$this
			->get('event_dispatcher')
			->dispatch(UserEvents::USER_PASSWORD_RECOVERY_ERROR, $passwordRecoveryErrorEvent);
	}

	/**
	 * Crée et renvoie le formulaire
	 * @param RecoverPassword $changePasswordModel
	 * @return \Symfony\Component\Form\Form
	 */
	private function getChangePasswordForm(RecoverPassword $changePasswordModel)
	{
		return $this->createForm(new RecoverPasswordType(), $changePasswordModel, array(
			'action' => $this->generateUrl('rudakUser_reinit_mail_answer', array(
				'hash' => $changePasswordModel->getHash()
			)),
			'method' => 'POST',
		));
	}

	/**
	 * Log le gars qui vient de recover son password avec succès
	 * @param User $User
	 * @param $request
	 */
	private function autoLogin(User $User, $request)
	{
		$token = new UsernamePasswordToken($User, null, "secured_area", $User->getRoles());
		$this->get("security.context")->setToken($token); //maintenant le gars est loggé

		//maintenant il faut dispatch l'event du login 'classique'
		$event = new InteractiveLoginEvent($request, $token);
		$this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
	}

	/**
	 * Renvoie un mot de passe a partir d'un user et d'un plain password
	 * @param User $user
	 * @param $plainPassword
	 * @return mixed
	 */
	private function createPassword(User $user, $plainPassword)
	{
		$encoder = $this->container
			->get('security.encoder_factory')
			->getEncoder($user);
		return $encoder->encodePassword($plainPassword, $user->getSalt());
	}
}
