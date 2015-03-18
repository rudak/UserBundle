<?php
/**
 * Created by PhpStorm.
 * User: rudak
 * Date: 18/03/2015
 * Time: 18:10
 */

namespace Rudak\UserBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add ('oldPassword', 'password', array(
			'label' => 'Ancien mot de passe'
		));
		$builder->add ('newPassword', 'repeated', array(
			'type' => 'password',
			'invalid_message' => 'Les mots de passe doivent correspondre.',
			'required' => true,
			'first_options' => array('label' => 'Nouveau mot de passe'),
			'second_options' => array('label' => 'Confirmez mot de passe'),
		));
		$builder->add ('submit', 'submit', array(
			'label' => 'Envoyer'
		));
	}

	public function setOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults (array(
			'data_class' => 'Rudak\UserBundle\Form\Model\ChangePassword',
		));
	}

	public function getName()
	{
		return 'change_passwd';
	}
}