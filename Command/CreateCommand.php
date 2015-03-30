<?php
namespace Rudak\UserBundle\Command;

use Rudak\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends ContainerAwareCommand
{

	private $em;

	protected function configure()
	{
		$this
			->setName('rudakuser:create')
			->setDescription('Creer un utilisateur.')
			->addArgument('username', InputArgument::OPTIONAL, 'Qui voulez vous saluer??')
			->addOption(
				'--admin',
				null,
				InputOption::VALUE_NONE,
				'Si défini, cet utilisateur sera admin'
			)
			->addOption(
				'--superadmin',
				null,
				InputOption::VALUE_NONE,
				'Si défini, cet utilisateur sera super_admin'
			)
			->addOption(
				'--blocked',
				null,
				InputOption::VALUE_NONE,
				'Si défini, cet utilisateur sera blocked'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$user     = new User();
		$username = $input->getArgument('username');
		$user->setUsername($username);
		$user->setEmail($user->getUsername() . '@website.fr');
		$user->setPlainPassword('0000');
		$user->setPassword($this->getEncodedPassword($user));
		$user->eraseCredentials();
		$user->setIsActive(true);
		$user->setEmailValidation(new \Datetime('NOW'));

		if ($input->getOption('admin')) {
			$user->addRole('ROLE_ADMIN');
		}
		if ($input->getOption('superadmin')) {
			$user->addRole('ROLE_SUPER_ADMIN');
		}
		if ($input->getOption('blocked')) {
			$user->setIsBlocked(true);
		}

		$this->getEm()->persist($user);
		$this->em->flush();
		$output->writeln('Utilisateur ' . $username . ' cree avec succes.');
	}


	/**
	 * Renvoie l'entity manager
	 * @return mixed
	 */
	private function getEm()
	{
		if ($this->em == null) {
			$this->em = $this->getContainer()->get('doctrine')->getManager();
		}
		return $this->em;
	}

	private function getEncodedPassword(User $user)
	{
		$factory = $this->getContainer()->get('security.encoder_factory');
		$encoder = $factory->getEncoder($user);
		return $encoder->encodePassword($user->getPlainPassword(), $user->getSalt());
	}
}