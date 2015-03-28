<?php

namespace Rudak\UserBundle\Controller;

use Rudak\UserBundle\Entity\User;
use Rudak\UserBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * User controller.
 *
 */
class UserController extends Controller
{

	/**
	 * Lists all User entities.
	 *
	 */
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();

		$entities = $em->getRepository('RudakUserBundle:User')->findAll();

		return $this->render('RudakUserBundle:User:index.html.twig', array(
			'entities' => $entities,
		));
	}

	/**
	 * Creates a new User entity.
	 *
	 */
	public function createAction(Request $request)
	{
		$entity = new User();
		$form   = $this->createCreateForm($entity);
		$form->handleRequest($request);

		if ($form->isValid()) {

			$entity->setIsActive(true);
			$entity->setPlainPassword($entity->getPassword());
			$newPassword = $this->createPassword($entity);
			$entity->setPassword($newPassword);

			$entity->setEmailValidation(new \Datetime('NOW'));

			$em = $this->getDoctrine()->getManager();
			$em->persist($entity);
			$em->flush();

			return $this->redirect($this->generateUrl('admin_user_show', array('id' => $entity->getId())));
		}

		return $this->render('RudakUserBundle:User:new.html.twig', array(
			'entity' => $entity,
			'form' => $form->createView(),
		));
	}

	/**
	 * Creates a form to create a User entity.
	 *
	 * @param User $entity The entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createCreateForm(User $entity)
	{
		$form = $this->createForm(new UserType(), $entity, array(
			'action' => $this->generateUrl('admin_user_create'),
			'method' => 'POST',
		));

		$form->add('submit', 'submit', array(
			'label' => 'Créer',
			'attr' => array(
				'class' => 'btn btn-success'
			)
		));

		return $form;
	}

	/**
	 * Displays a form to create a new User entity.
	 *
	 */
	public function newAction()
	{
		$entity = new User();
		$form   = $this->createCreateForm($entity);

		return $this->render('RudakUserBundle:User:new.html.twig', array(
			'entity' => $entity,
			'form' => $form->createView(),
		));
	}

	/**
	 * Finds and displays a User entity.
	 *
	 */
	public function showAction($id)
	{
		$em = $this->getDoctrine()->getManager();

		$entity = $em->getRepository('RudakUserBundle:User')->find($id);

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find User entity.');
		}

		$deleteForm = $this->createDeleteForm($id);

		return $this->render('RudakUserBundle:User:show.html.twig', array(
			'entity' => $entity,
			'delete_form' => $deleteForm->createView(),
		));
	}

	/**
	 * Displays a form to edit an existing User entity.
	 *
	 */
	public function editAction($id)
	{
		$em = $this->getDoctrine()->getManager();

		$entity = $em->getRepository('RudakUserBundle:User')->find($id);

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find User entity.');
		}

		$editForm   = $this->createEditForm($entity);
		$deleteForm = $this->createDeleteForm($id);

		return $this->render('RudakUserBundle:User:edit.html.twig', array(
			'entity' => $entity,
			'edit_form' => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
		));
	}

	/**
	 * Creates a form to edit a User entity.
	 *
	 * @param User $entity The entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createEditForm(User $entity)
	{
		$form = $this->createForm(new UserType(true), $entity, array(
			'action' => $this->generateUrl('admin_user_update', array('id' => $entity->getId())),
			'method' => 'PUT',
		));

		$form->add('submit', 'submit', array(
			'label' => 'Modifier',
			'attr' => array(
				'class' => 'btn btn-success'
			)
		));

		return $form;
	}

	/**
	 * Edits an existing User entity.
	 *
	 */
	public function updateAction(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();

		$entity = $em->getRepository('RudakUserBundle:User')->find($id);

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find User entity.');
		}

		$deleteForm = $this->createDeleteForm($id);
		$editForm   = $this->createEditForm($entity);
		$editForm->handleRequest($request);

		if ($editForm->isValid()) {
			$em->flush();

			return $this->redirect($this->generateUrl('admin_user_edit', array('id' => $id)));
		}

		return $this->render('RudakUserBundle:User:edit.html.twig', array(
			'entity' => $entity,
			'edit_form' => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
		));
	}

	/**
	 * Deletes a User entity.
	 *
	 */
	public function deleteAction(Request $request, $id)
	{
		$form = $this->createDeleteForm($id);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$em     = $this->getDoctrine()->getManager();
			$entity = $em->getRepository('RudakUserBundle:User')->find($id);

			if (!$entity) {
				throw $this->createNotFoundException('Unable to find User entity.');
			}

			$em->remove($entity);
			$em->flush();
		}

		return $this->redirect($this->generateUrl('admin_user'));
	}

	/**
	 * Creates a form to delete a User entity by id.
	 *
	 * @param mixed $id The entity id
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createDeleteForm($id)
	{
		return $this->createFormBuilder()
					->setAction($this->generateUrl('admin_user_delete', array('id' => $id)))
					->setMethod('DELETE')
					->add('submit', 'submit', array(
						'label' => 'Supprimer',
						'attr' => array(
							'class' => 'btn btn-danger'
						)
					))
					->getForm();
	}

	private function createPassword(User $user)
	{
		$encoder = $this->container
			->get('security.encoder_factory')
			->getEncoder($user);
		return $encoder->encodePassword($user->getPassword(), $user->getSalt());

	}
}
