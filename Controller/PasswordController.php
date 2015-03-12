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
			$user->setHash(sha1(md5(uniqid(null, true))));
			$this->sendMail($user);
			$this->addFlash('notice', 'Email de récupération envoyé');
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
					'hash' => $user->getHash()
				), true)
			)));
		$this->get('mailer')->send($message);
	}

	/**
	 * Traite la réponse du mail, et renvoie vers le formulaire de réinititlisation de mot de passe
	 * @param $hash
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function emailAnswerAction($hash)
	{
		$em   = $this->getDoctrine()->getManager();
		$user = $em->getRepository('RudakUserBundle:User')->getUserByHash($hash);
		if (!$user) {
			$this->addFlash('notice', 'Impossible de trouver une correspondance avec ce hash.');
			return $this->redirectToRoute('homepage');
		}
		$changePasswordModel = new ChangePassword();
		$changePasswordModel->setHash($hash);
		$form = $this->getChangePasswordForm($changePasswordModel);
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
			'action' => $this->generateUrl('rudakUser_password_lost_reinit'),
			'method' => 'POST',
		));
	}

	/**
	 * Traite la reception du changement de mot de passe
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function recordNewPasswordAction(Request $request)
	{
		$changePasswordModel = new ChangePassword();
		$form                = $this->getChangePasswordForm($changePasswordModel);
		$form->handleRequest($request);
		if ($form->isValid()) {
			$em   = $this->getDoctrine()->getManager();
			$user = $em->getRepository('RudakUserBundle:User')->getUserByHash($changePasswordModel->getHash());
			if (!$user) {
				// user perdu en route
				throw $this->createNotFoundException('User perdu en route');
			}
			$password = $this->createPassword($user, $changePasswordModel->getNewPassword());
			$user->setPassword($password);
			$em->persist($user);
			$em->flush();
			$this->addFlash('notice', 'Mot de passe réinitialisé avec succès');
			return $this->redirectToRoute('homepage');
		}
		return $this->render('RudakUserBundle:Password:init-form.html.twig', array(
			'form' => $form->createView()
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
