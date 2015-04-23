<?php
namespace Rudak\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigCommand extends ContainerAwareCommand
{

	protected function configure()
	{
		$this
			->setName('rudakuser:config:dump')
			->setDescription('Recuperer la configuration du bundle.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$container = $this->getContainer();
		$config    = $container->getParameter('rudak.user.config');
		if (is_array($config)) {
			$output->writeln("\nConfiguration du RudakUserBundle\n");
			foreach ($config as $key => $value) {
				$output->writeln($key . ' : ' . ($value == false ? 'false' : $value));
			}
		} else {
			$output->writeln('Erreur de configuration du bundle');
		}

	}

}