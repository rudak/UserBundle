<?php

namespace Rudak\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecordType extends AbstractType
{

	/**
	 * @param FormBuilderInterface $builder
	 * @param array                $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('username', 'text', array(
				'label' => 'Utilisateur'
			))
			->add('password', 'repeated', [
				'type'            => 'password',
				'invalid_message' => 'Les mots de passe doivent correspondre',
				'options'         => ['required' => true],
				'first_options'   => ['label' => 'Mot de passe'],
				'second_options'  => ['label' => 'Mot de passe (Confirmation)'],
			])
			->add('email')
			->add('Envoyer', 'submit', [
				'attr' => [
					'class' => 'submit_btn'
				]
			]);
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => 'Rudak\UserBundle\Entity\User'
		]);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'rudak_userbundle_user';
	}
}
