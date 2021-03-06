<?php

namespace Rudak\UserBundle\Controller;

use Rudak\UserBundle\Entity\User;
use Rudak\UserBundle\Event\BaseEvent;
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

	public function  lostAction()
	{
		return $this->render('RudakUserBundle:Password:lost.html.twig');
	}

	public function changePasswordAction(Request $request)
	{
		if (!$this->getUser()) {
			$this->addFlash('warning', 'Vous devez etre loggé pour modifier votre mot de passe.');

			return $this->redirectToRoute($this->getHomepageRoute());
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
				$BaseEvent = new BaseEvent($user);
				// evenement modification de mot de passe
				$this
					->get('event_dispatcher')
					->dispatch(UserEvents::USER_PASSWORD_CHANGE_SUCCESS, $BaseEvent);
				$this->addFlash('success', 'Mot de passe changé avec succès.');

				return $this->redirect($this->generateUrl('rudakUser_profile'));
			} else {
				// formulaire invalide
				$BaseEvent = new BaseEvent($user);
				$this
					->get('event_dispatcher')
					->dispatch(UserEvents::USER_PASSWORD_CHANGE_ERROR, $BaseEvent);
			}
		}

		return $this->render('RudakUserBundle:Password:change.html.twig', array(
			'form' => $form->createView()
		));

	}

	/**
	 * Recherche un user selon l'info donnée (user ou email) et envoie un mail pour la recup
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function findUserAction(Request $request)
	{
		$data = $request->request->get('lostpwd_user_input');
		$em   = $this->getDoctrine()->getManager();
		$user = $em->getRepository('RudakUserBundle:User')->checkIfUserExists($data);

		if ($user && $user instanceof User) {
			$baseEvent = new BaseEvent($user);
			$this
				->get('event_dispatcher')
				->dispatch(UserEvents::USER_PASSWORD_LOST_REQUEST, $baseEvent);
			$em->persist($user);
			$em->flush();
		} else {
			$this->addFlash('danger', 'Ce nom d’utilisateur ne correspond pas au nom enregistré sur nos serveurs.');
		}

		return $this->redirectToRoute($this->getHomepageRoute());
	}

	/**
	 * Traite la réponse du mail, et renvoie vers le formulaire de réinititlisation de mot de passe
	 *
	 * @param $hash
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function emailAnswerAction(Request $request, $hash)
	{
		$em   = $this->getDoctrine()->getManager();
		$user = $em->getRepository('RudakUserBundle:User')->getUserByHash($hash);

		if (!$user) {
			$this->addFlash('danger', 'Impossible de trouver une correspondance avec cette clé de réinitialisation.');

			return $this->redirectToRoute($this->getHomepageRoute());
		}

		if (new \Datetime('NOW') > $user->getSecurityHashExpireAt()) {
			$this->launchErrorEvent($user);
			$this->addFlash('warning', "Le code de réinitialisation est expiré, merci de recommencer la procédure.");

			return $this->redirectToRoute('rudakUser_lost_pwd');
		}
		// Récupération du formulaire
		$changePasswordModel = new RecoverPassword();
		$changePasswordModel->setHash($hash);
		$form = $this->getChangePasswordForm($changePasswordModel);

		$form->handleRequest($request);
		if ($form->isValid()) {
			$user->setPlainPassword($changePasswordModel->getNewPassword());
			$BaseEvent = new BaseEvent($user);
			$this
				->get('event_dispatcher')
				->dispatch(UserEvents::USER_PASSWORD_RECOVERED, $BaseEvent);

			$em->persist($user);
			$em->flush();

			$this->addFlash('success', "Le mot de passe a été changé avec succès.");
			// si l'autologin est ok
			$rudakConfig = $this->container->getParameter('rudak.user.config');
			if ($rudakConfig['autologin_before_reinit']) {
				$this->autoLogin($user, $request);
			}

			return $this->redirectToRoute($this->getHomepageRoute());
		}

		return $this->render('RudakUserBundle:Password:init-form.html.twig', array(
			'form' => $form->createView(),
			'user' => $user
		));
	}

	/**
	 * Event correspondant a une erreur de reinitialisation de password
	 *
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
	 *
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
	 *
	 * @param User $User
	 * @param      $request
	 */
	private function autoLogin(User $User, $request)
	{
		$token = new UsernamePasswordToken($User, null, "secured_area", $User->getRoles());
		$this->get("security.context")->setToken($token); //maintenant le gars est loggé

		//maintenant il faut dispatch l'event du login 'classique'
		$event = new InteractiveLoginEvent($request, $token);
		$this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
	}


	private function getHomepageRoute()
	{
		$config = $this->container->getParameter('rudak.user.config');
		return $config['homepage_route'];
	}
}
