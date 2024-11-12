<?php

namespace Command\App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class Update extends Command {

	protected function configure(): void
	{
		$this->setName("update");
		$this->setDescription("Unee updater");
		$this->setHelp('This command allows you to update Unee app');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{


		return Command::SUCCESS;
	}


}
