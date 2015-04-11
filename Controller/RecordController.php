<?php

namespace Rudak\UserBundle\Controller;

use Rudak\UserBundle\Entity\User;
use Rudak\UserBundle\Event\BaseEvent;
use Rudak\UserBundle\Event\UserEvents;
use Rudak\UserBundle\Form\RecordType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class RecordController extends Controller
{

	public function newAction()
	{
		$User = new User();
		$form = $this->createNewForm($User);

		return $this->render('RudakUserBundle:Record:new.html.twig', [
			'form' => $form->createView()
		]);
	}

	private function createNewForm($user)
	{
		$form = $this->createForm(new RecordType(), $user, [
			'action' => $this->generateUrl('record_create')
		]);

		return $form;
	}

	public function createAction(Request $request)
	{

		$User = new User();
		$form = $this->createNewForm($User);
		$form->handleRequest($request);
		if ($request->getMethod() == 'POST') {
			if ($form->isValid()) {
				$User->setPassword($this->createPassword($User));

				if (!$this->checkDuplicate($User)) {

					$BaseEvent = new BaseEvent($User);
					$this
						->get('event_dispatcher')
						->dispatch(UserEvents::USER_CREATED, $BaseEvent);
					$this
						->get('event_dispatcher')
						->dispatch(UserEvents::USER_POST_REGISTER, $BaseEvent);

					$em = $this->getDoctrine()->getManager();
					$em->persist($User);
					$em->flush();

					$this->addFlash('info', 'Utilisateur ' . $User->getUsername() . ' créé. Un email
					vient de vous etre envoyé, veuillez cliquer sur le lien de validation pour confirmer
					votre adresse mail et valider l\'inscription. Merci beaucoup. ');

					return $this->redirectToRoute($this->getHomepageRoute());
				} else {
					$this->addFlash(
						'warning',
						'Utilisateur possedant le meme pseudo ou adresse email existe deja dans la base'
					);
				}
			} else {
				$this->addFlash(
					'warning',
					'Formulaire invalide !'
				);
			}
		} else {
			$this->addFlash('danger', 'Methode non autorisée.');
		}

		return $this->redirect($this->generateUrl('record_new'));
	}

	private function createPassword(User $user)
	{
		$encoder = $this->container
			->get('security.encoder_factory')
			->getEncoder($user);

		return $encoder->encodePassword($user->getPassword(), $user->getSalt());

	}

	private function checkDuplicate(User $user)
	{
		$em   = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('RudakUserBundle:User');

		if ($repo->getUserIfExists($user->getUsername()) instanceof User) {
			return true;
		} else {
			return false;
		}
	}

	public function validationAction($hash, Request $request)
	{
		$em   = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('RudakUserBundle:User');

		$User = $repo->getUserByHash($hash);
		if (!$User) {
			// correspond a rien
			throw $this->createNotFoundException('Utilisateur impossible a trouver avec le hash "' . $hash . '".');
		}
		if (true == $User->getIsActive()) {
			// utilisateur deja actif
			throw $this->createNotFoundException('Cet utilisateur a déja été activé !');
		}

		$BaseEvent = new BaseEvent($User);
		$this
			->get('event_dispatcher')
			->dispatch(UserEvents::USER_EMAIL_VALIDATION, $BaseEvent);

		$token = new UsernamePasswordToken($User, null, "secured_area", $User->getRoles());
		$this->get("security.context")->setToken($token); //maintenant le gars est loggé

		//maintenant il faut dispatch l'event du login 'classique'
		$event = new InteractiveLoginEvent($request, $token);
		$this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

		$em->persist($User);
		$em->flush();

		$this->addFlash(
			'success',
			'Email de l\'utilisateur ' . $User->getUsername() . ' validée.'
		);

		return $this->redirectToRoute($this->getHomepageRoute());
	}

	private function getHomepageRoute()
	{
		$config = $this->container->getParameter('rudak.user.config');
		return $config['homepage_route'];
	}
}
