<?php

namespace command\make;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;




class Migration extends Command {

	protected static $defaultName = 'make:migration';
	protected static $defaultDescription = 'Creates a migration file.';

	protected function configure(): void
	{
		$this->setName('make:migration');
		$this->setDescription('create a migration file');
		$this->setHelp('This command allows you to create a migration file.');
		$this->addArgument('name', InputArgument::REQUIRED, 'The name of the migration');

	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$name = $input->getArgument('name');

		// Validation of the migration name
		if(!is_string($name) || empty($name))
		{
			$output->writeln("<error>Migration name cannot be null.</error>");
			return Command::INVALID;
		}

		// Check lowercase and allowed characters (alphanumeric, -, _)
		if(!preg_match('/^[a-z0-9_-]+$/', $name))
		{
			$output->writeln("<error>The migration name should be lowercase and can only contain alphanumeric characters, '-', and '_'.</error>");
			return Command::INVALID;
		}

		$migration_dir = APP_PATH.'/migration';

		if(!is_dir($migration_dir))
		{
			mkdir($migration_dir, 0755, true);
			$output->writeln("Created directory: $migration_dir");
		}

		$timestamp = date('YmdHis');
		$timestamp_sql = substr($timestamp, 0, 4).'-'.substr($timestamp, 4, 2).'-'.substr($timestamp, 6, 2).' '.substr($timestamp, 8, 2).':'.substr($timestamp, 10, 2).':'.substr($timestamp, 12, 2);
		$filename = "{$migration_dir}/{$timestamp}_{$name}.php";

		if(file_exists($filename))
		{
			$output->writeln("<error>Migration file already exists: $filename</error>");
			return Command::FAILURE;
		}

		$tpl_migration = APP_PATH.'/core/command/make/migration.tpl.php';
		$migration_content = file_get_contents($tpl_migration);
		$migration_content = str_replace('[name]', $name, $migration_content);
		$migration_content = str_replace('[timestamp_sql]', $timestamp_sql, $migration_content);
		file_put_contents($filename, $migration_content);


		// Output success message
		$output->writeln("Migration file created: `$filename`, run `unee migrate` to execute");
		return Command::SUCCESS;

	}


}