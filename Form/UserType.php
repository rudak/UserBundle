<?php

namespace Rudak\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
	private $editMode;

	function __construct($editMode = false)
	{
		$this->editMode = $editMode;
	}

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('username');
		if (false === $this->editMode) {
			$builder->add('password', 'repeated', [
				'type' => 'password',
				'invalid_message' => 'Les mots de passe doivent correspondre',
				'options' => ['required' => true],
				'first_options' => ['label' => 'Mot de passe'],
				'second_options' => ['label' => 'Mot de passe (Confirmation)'],
			]);
		}
		$builder
			->add('isActive', 'checkbox', array(
				'label' => 'Activé',
				'required' => false
			))
			->add('isBlocked', 'checkbox', array(
				'label' => 'Bloqué',
				'required' => false
			));
		$builder->add('roles', 'choice', array(
			'choices' => array(
				'ROLE_USER' => 'Utilisateur',
				'ROLE_MODO' => 'Modérateur',
				'ROLE_ADMIN' => 'Administrateur'
			),
			'multiple' => true
		))
				->add('email', 'email');
	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Rudak\UserBundle\Entity\User'
		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'rudak_userbundle_user';
	}
}
