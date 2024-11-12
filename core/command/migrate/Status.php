<?php

namespace command\migrate;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;


class Status extends Command {

	protected function configure(): void
	{
		$this->setName('migrate:status');
		$this->setDescription('get all migration operation executed');
		$this->setHelp('This command allows you to visualize all migration operation.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		global $DB;

		$migrations_done = $DB->query('select date, filename from xcore_migration order by date desc')->fetchAll();

		if(!$migrations_done)
		{
			$output->writeln("no migration found");
			return Command::SUCCESS;
		}

		$table = new Table($output);
		$table->setHeaders(['Date', 'Filename']);
		foreach($migrations_done as $migration)
		{
			$table->addRow([$migration['date'], $migration['filename']]);
		}
		$table->render();

		return Command::SUCCESS;

	}


}