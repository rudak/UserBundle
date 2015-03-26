<?php
namespace Rudak\UserBundle\Command;

use Rudak\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DemoteCommand extends ContainerAwareCommand
{

	private $em;

	protected function configure()
	{
		$this
			->setName('rudakuser:demote')
			->setDescription('Retirer les promotions d\'un utilisateur.')
			->addArgument('username', InputArgument::OPTIONAL, 'Qui voulez vous modifier ?');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{

		$username = $input->getArgument('username');

		$user = $this->getEm()->getRepository('RudakUserBundle:User')->loadUserByUsername($username);
		if (!$user) {
			$output->writeln('Utilisateur introuvable');
			return;
		}

		$user->removeRole(User::ROLE_MODERATOR);
		$user->removeRole(User::ROLE_ADMIN);
		$user->removeRole(User::ROLE_SUPER_ADMIN);

		$this->em->persist($user);
		$this->em->flush();
		$output->writeln($username . ' demote successfull.');
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