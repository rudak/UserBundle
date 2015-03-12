<?php
namespace Rudak\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('hash', 'hidden')
			->add('newPassword', 'repeated', array(
				'type' => 'password',
				'invalid_message' => 'Les mots de passe doivent correspondre.',
				'required' => true,
				'first_options' => array('label' => 'Mot de passe'),
				'second_options' => array('label' => 'Confirmez mot de passe'),
			))
			->setMethod('POST')
			->add('Envoyer', 'submit');
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Rudak\UserBundle\Form\Model\ChangePassword',
		));
	}

	public function getName()
	{
		return 'change_passwd';
	}
}