<?php

namespace command\migrate;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;



class Migrate extends Command {

	protected function configure(): void
	{
		$this->setName('migrate');
		$this->setDescription('execute migration');
		$this->setHelp('This command allows you to execute migration.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		global $DB;

		$migration_dir = APP_PATH.'/migration';

		if(!is_dir($migration_dir))
		{
			$output->writeln("<error>No migration directory found at `$migration_dir`</error>");
			return Command::FAILURE;
		}

		$files = glob($migration_dir . '/*.php');

		if(empty($files))
		{
			$output->writeln("<info>No migrations found to execute.</info>");
			return Command::SUCCESS;
		}

		// get done migration
		$migrations_done = (array)$DB->query('select filename from xcore_migration')->fetchAllOne();

		$migration_executed = 0;
		foreach($files as $file)
		{
			if(in_array(basename($file), $migrations_done))
				continue;

			$output->writeln("- Executing migration: " . basename($file));
			$migration = require $file;

			// Ensure it has an 'up' method
			if(!is_object($migration) || !method_exists($migration, 'up'))
			{
				$output->writeln("<error>Migration `".basename($file)."` does not have an up() method.</error>");
				return Command::FAILURE;
			}


			$migration->up($DB);
			$output->writeln("<info> - Migration `".basename($file)."` executed successfully.</info>");

			// store in database
			$r = [];
			$r['date'] = now();
			$r['filename'] = basename($file);
			$DB->table('xcore_migration')->insert($r);
			$migration_executed++;

		}

		// Output success message
		$msg = "Migration executed: {$migration_executed}";
		if(!$migration_executed)
			$msg = "Migrations are already up to date";

		$output->writeln("");
		$output->writeln($msg);
		return Command::SUCCESS;
	}


}