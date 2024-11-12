<?php

namespace command\make;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;



class Entity extends Command {

	protected static $defaultName = 'make:entity';
	protected static $defaultDescription = 'Creates an entity.';

	protected function configure(): void
	{
		$this->setHelp('This command allows you to make an entity');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		// or return this if some error happened during the execution => return Command::FAILURE;
		// or return this to indicate incorrect command usage; e.g. invalid options or missing arguments (it's equivalent to returning int(2)) => return Command::INVALID
		$helper = $this->getHelper('question');
		$question = new Question('Please enter entity name : ');
		$question->setValidator(function (string $answer): string {

			if(!is_string($answer) || empty($answer))
				throw new \RuntimeException("Entity can not be null");

			if(file_exists("entity/{$answer}.php"))
				throw new \RuntimeException("Entity already exists");

			return $answer;
		});

		$question->setMaxAttempts(3);


		$name = $helper->ask($input, $output, $question);




		return Command::SUCCESS;
	}


}