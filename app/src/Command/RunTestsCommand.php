<?php

declare(strict_types = 1);

namespace App\Command;

use Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use function ob_flush;
use function ob_get_level;
use function shell_exec;

class RunTestsCommand extends Command {
	protected static $defaultName = 'app:run-tests';
	
	public function __construct(private ParameterBagInterface $parameterBag, string $name = null) {
		parent::__construct($name);
	}
	
	protected function configure(): void {
		$this->setDescription(
			'Drop and recreate all table schemas. Run fixtures. Then run all the tests in the app. '
			. 'This command takes some time to refresh the fixtures and it is only required once to create the fixtures and save them to file.'
		)
			 ->addArgument('test', InputArgument::OPTIONAL)
			 ->addOption(
				 'filter',
				 null,
				 InputOption::VALUE_REQUIRED
			 )
			 ->addOption(
				 'coverage',
				 null,
				 InputOption::VALUE_REQUIRED,
				 'Should coverage be run?',
				 false
			 )
		;
	}
	
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$io = new SymfonyStyle($input, $output);
		/** @var Application $app */
		$app = $this->getApplication();
		
		if ($this->parameterBag->get('kernel.environment') !== 'dev') {
			$io->error('Cannot run command in production.');
			return Command::FAILURE;
		}
		$io->info(
			[
				'Kikopolis has need of tests. Beginning procedure.',
				'=================================================',
			]
		);
		try {
			$app->find('app:doctrine:fresh')
				->run(new ArrayInput([]), $output)
			;
		} catch (Exception $e) {
			$io->error('Error in running "app:doctrine:fresh" in "app:run-tests".');
			$app->renderThrowable($e, $output);
			return Command::FAILURE;
		}
//		shell_exec('rmdir "var\db"');
//		shell_exec('mkdir "var\db"');
//		shell_exec("symfony db:dump > var/db/test_db.sql");
		// Run phpunit itself
		$testCmd = 'php bin/phpunit';
		if ($input->getOption('filter')) {
			$testCmd .= sprintf(' --filter %s', (string) $input->getOption('filter'));
		}
		if ($input->hasArgument('test')) {
			$testCmd .= sprintf(' %s', (string) $input->getArgument('test'));
		}
		if ($input->getOption('coverage')) {
			$testCmd .= ' --coverage-html ./coverage';
		}
		try {
			$this->runShellCommand($testCmd);
		} catch (Exception $e) {
			$app->renderThrowable($e, $output);
			return Command::FAILURE;
		}
		$io->newLine();
		$io->success('DB is fresh. Fixtures are done and hopefully all tests pass!');
		$io->success('Eternal is the glory of magnificent kikopolis!');
		$io->newLine();
		return Command::SUCCESS;
	}
	
	/**
	 * @param   string   $cmd
	 * @throws Exception
	 */
	private function runShellCommand(string $cmd): void {
		while (ob_get_level() > 0) {
			ob_flush();
		}
		$proc = popen($cmd, 'r');
		if ($proc === false) {
			throw new Exception('"runShellCommand" does not go brrrp...');
		}
		while (! feof($proc)) {
			echo fread($proc, 4096);
			flush();
		}
	}
}
