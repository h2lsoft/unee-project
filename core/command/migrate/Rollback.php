<?php

namespace command\migrate;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;



class Rollback extends Command {

	protected function configure(): void
	{
		$this->setName('migrate:rollback');
		$this->setDescription('rollback the last migration operation');
		$this->setHelp('This command allows you to rollback the last migration operation.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		global $DB;
		$migration_dir = APP_PATH.'/migration';

		// check dir
		if(!is_dir($migration_dir))
		{
			$output->writeln("<error>No migration directory found at `$migration_dir`</error>");
			return Command::FAILURE;
		}


		$last_migration = $DB->query('select id, filename from xcore_migration order by date desc limit 1')->fetch();
		if(!$last_migration)
		{
			$output->writeln("No migration found");
			return Command::SUCCESS;
		}

		// file exists
		if(!file_exists("{$migration_dir}/{$last_migration['filename']}"))
		{
			$output->writeln("<error>migration file not found `{$migration_dir}/{$last_migration['filename']}`</error>");
			return Command::FAILURE;
		}

		// down execute
		$output->writeln("\nExecuting rollback: `{$last_migration['filename']}`");
		$migration = require "{$migration_dir}/{$last_migration['filename']}";
		if(!is_object($migration) || !method_exists($migration, 'down'))
		{
			$output->writeln("<error>Migration file `{$last_migration['filename']}` does not have an down() method.</error>");
			return Command::FAILURE;
		}

		$migration->down($DB);
		$output->writeln("- Migration `{$last_migration['filename']}` executed successfully.");

		// remove entry
		$DB->query("delete from xcore_migration where id = {$last_migration['id']}");


		$msg = "<info>Rollback executed successfully</info>";

		$output->writeln("");
		$output->writeln($msg);
		return Command::SUCCESS;

	}


}