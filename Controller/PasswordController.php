<?php

namespace Rudak\UserBundle\Controller;

use Rudak\UserBundle\Entity\User;
use Rudak\UserBundle\Form\ChangePasswordType;
use Rudak\UserBundle\Form\LostPwdType;
use Rudak\UserBundle\Form\Model\ChangePassword;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PasswordController extends Controller
{
	public function lostAction()
	{
		return $this->render('RudakUserBundle:Password:lost.html.twig');
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
		} else {
			$this->addFlash('notice', 'Impossible de trouver un utilisateur correspondant.');
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
			$this->addFlash('notice', "Le code de réinitialisation est expiré, merci de recommencer la procedure.");
			return $this->redirectToRoute('rudakUser_lost_pwd');
		}
		$changePasswordModel = new ChangePassword();
		$changePasswordModel->setHash($hash);
		$form = $this->getChangePasswordForm($changePasswordModel);

		$form->handleRequest($request);
		if ($form->isValid()) {
			$user->setRecoveryHash(null);
			$user->setRecoveryExpireAt(null);
			$user->setPassword($this->createPassword($user, $changePasswordModel->getNewPassword()));
			$em->persist($user);
			$em->flush();
			$this->addFlash('notice', "Le mot de passe a été changé avec succès.");
			return $this->redirectToRoute('homepage');
		}
		return $this->render('RudakUserBundle:Password:init-form.html.twig', array(
			'form' => $form->createView(),
			'user' => $user
		));
	}

	/**
	 * Crée et renvoie le formulaire
	 * @param ChangePassword $changePasswordModel
	 * @return \Symfony\Component\Form\Form
	 */
	private function getChangePasswordForm(ChangePassword $changePasswordModel)
	{
		return $this->createForm(new ChangePasswordType(), $changePasswordModel, array(
			'action' => $this->generateUrl('rudakUser_reinit_mail_answer', array(
				'hash' => $changePasswordModel->getHash()
			)),
			'method' => 'POST',
		));
	}

	private function createPassword(User $user, $plainPassword)
	{
		$encoder = $this->container
			->get('security.encoder_factory')
			->getEncoder($user);
		return $encoder->encodePassword($plainPassword, $user->getSalt());

	}

}
