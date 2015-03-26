<?php
namespace Rudak\UserBundle\Command;

use Rudak\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SecurityCheckCommand extends ContainerAwareCommand
{

	private $em;

	protected function configure()
	{
		$this
			->setName('rudakuser:securitycheck')
			->setDescription('Check for expired security hashes.')
			->addOption(
				'--delete',
				null,
				InputOption::VALUE_NONE,
				'Si défini, les hash et dates de validités expirés seront supprimés'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$expiredSecurityHashes = $this->getExpiredSecurityHashes();
		if (count($expiredSecurityHashes)) {
			foreach ($expiredSecurityHashes as $user) {
				$output->writeln($this->getOutputLine($user));
				if ($input->getOption('delete')) {
					$this->initSecurityHash($user);
					$output->writeln('Suppression du hash.');
					$this->em->persist($user);
				}
			}
			if ($input->getOption('delete')) {
				$this->em->flush();
			}
		} else {

			$output->writeln('Aucun hash a expiration.');
		}
	}

	private function initSecurityHash(User $user)
	{
		$user->setSecurityHash(null);
		$user->setSecurityHashExpireAt(null);
	}

	private function getOutputLine(User $user)
	{
		$str = $user->getUsername() . ', hash exprire le ' . $user->getSecurityHashExpireAt()->format('d/m/Y \a H:i:s');
		return $str;
	}

	/**
	 * Renvoie les utilisateurs avec des hash expirés
	 * @return
	 */
	private function getExpiredSecurityHashes()
	{
		$em = $this->getEm();
		return $em->getRepository('RudakUserBundle:User')->getExpiredHashes();
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