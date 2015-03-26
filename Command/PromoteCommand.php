<?php
namespace Rudak\UserBundle\Command;

use Rudak\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PromoteCommand extends ContainerAwareCommand
{

	private $em;

	protected function configure()
	{
		$this
			->setName('rudakuser:promote')
			->setDescription('Donner une promotion a un utilisateur.')
			->addArgument('username', InputArgument::OPTIONAL, 'Qui voulez vous modifier ?')
			->addOption(
				'--moderator',
				null,
				InputOption::VALUE_NONE,
				'Si défini, cet utilisateur sera moderateur.'
			)
			->addOption(
				'--admin',
				null,
				InputOption::VALUE_NONE,
				'Si défini, cet utilisateur sera admin.'
			)
			->addOption(
				'--superadmin',
				null,
				InputOption::VALUE_NONE,
				'Si défini, cet utilisateur sera super_admin.'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{

		$username = $input->getArgument('username');

		$user = $this->getEm()->getRepository('RudakUserBundle:User')->loadUserByUsername($username);
		if (!$user) {
			$output->writeln('Utilisateur introuvable');
			return;
		}

		if ($input->getOption('moderator')) {
			$user->addRole(User::ROLE_MODERATOR);
		}
		if ($input->getOption('admin')) {
			$user->addRole(User::ROLE_ADMIN);
		}
		if ($input->getOption('superadmin')) {
			$user->addRole(User::ROLE_SUPER_ADMIN);
		}

		$this->em->persist($user);
		$this->em->flush();
		$output->writeln($username . ' promu.');
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
}