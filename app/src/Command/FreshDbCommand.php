<?php

declare(strict_types = 1);

namespace App\Command;

use Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FreshDbCommand extends Command {
	protected static              $defaultName = 'app:doctrine:fresh';
	private ParameterBagInterface $parameterBag;
	
	public function __construct(ParameterBagInterface $parameterBag, string $name = null) {
		parent::__construct($name);
		$this->parameterBag = $parameterBag;
	}
	
	protected function configure(): void {
		$this->setDescription('Drop and recreate all table schemas. Run fixtures.');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->setHidden(true);
		$io = new SymfonyStyle($input, $output);
		/** @var Application $app */
		$app = $this->getApplication();
		if ($this->parameterBag->get('kernel.environment') === 'prod') {
			$io->error('Cannot run command in production.');
			return Command::FAILURE;
		}
		$io->info(
			[
				'Kikopolis must be free of flaws! Reset the DB!',
				'==============================================',
			]
		);
		try {
			$app->find('doctrine:database:drop')
				->run(new ArrayInput(['command' => 'doctrine:database:drop', '--force' => true]), $output)
			;
			$app->find('doctrine:database:create')
				->run(new ArrayInput(['command' => 'doctrine:database:create']), $output)
			;
			$app->find('doctrine:schema:update')
				->run(new ArrayInput(['command' => 'doctrine:schema:update', '--force' => true]), $output)
			;
			$loadFixtsInput = new ArrayInput(['command' => 'doctrine:fixtures:load']);
			$loadFixtsInput->setInteractive(false);
			$app->find('doctrine:fixtures:load')
				->run($loadFixtsInput, $output)
			;
		} catch (Exception $e) {
			$io->error('Error in running "app:doctrine:fresh".');
			$app->renderThrowable($e, $output);
			return Command::FAILURE;
		}
		$io->newLine();
		$io->success('Kikopolis is FREE!!! DB is fresh!');
		$io->newLine();
		return Command::SUCCESS;
	}
}
