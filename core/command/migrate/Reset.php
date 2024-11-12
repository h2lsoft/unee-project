<?php

namespace command\migrate;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;



class Reset extends Command {

	protected function configure(): void
	{
		$this->setName('migrate:reset');
		$this->setDescription('reset all migration operation');
		$this->setHelp('This command allows you to rollback all the migration.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		global $DB;
		$migration_dir = APP_PATH.'/migration';

		// Add confirmation prompt
		$helper = $this->getHelper('question');
		$question = new ConfirmationQuestion(
			'<question>Are you sure you want to reset all migrations? This action is irreversible. (y/n)</question> ',
			false // Default answer is "no"
		);

		if (!$helper->ask($input, $output, $question)) {
			$output->writeln("<comment>Reset operation aborted by user.</comment>");
			return Command::SUCCESS;
		}


		// check dir
		if(!is_dir($migration_dir))
		{
			$output->writeln("<error>No migration directory found at `$migration_dir`</error>");
			return Command::FAILURE;
		}

		// get all migrations executed
		$migrations_done = $DB->query('select * from xcore_migration order by date desc')->fetchAll();

		if(!$migrations_done)
		{
			$output->writeln("No migrations were found");
			return Command::SUCCESS;
		}

		foreach($migrations_done as $m)
		{
			// check file exists
			if(!file_exists("{$migration_dir}/{$m['filename']}"))
			{
				$output->writeln("<error>Migration `{$migration_dir}/{$m['filename']}` not found.</error>");
				return Command::FAILURE;
			}

			// check method down exists
			$migration = require "{$migration_dir}/{$m['filename']}";
			if(!is_object($migration) || !method_exists($migration, 'down'))
			{
				$output->writeln("<error>Rollback `{$m['filename']}` does not have an down() method.</error>");
				return Command::FAILURE;
			}
		}

		// execute all and remove from database
		foreach($migrations_done as $m)
		{
			$output->writeln("\nExecuting rollback: `{$m['filename']}`");
			$migration = require "{$migration_dir}/{$m['filename']}";
			$migration->down($DB);
			$output->writeln("- rollback `{$m['filename']}` executed successfully");

			// remove from database
			$DB->query("delete from xcore_migration where id = {$m['id']}");
		}

		$msg = "<info>Reset executed successfully</info>";

		$output->writeln("");
		$output->writeln($msg);
		return Command::SUCCESS;

	}


}