<?php

namespace command\make;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class Entity extends Command {

	protected function configure(): void
	{
		$this->setName('make:entity');
		$this->setDescription('create a model file');
		$this->setHelp('This command allows you to create a model file.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		global $DB;

		$io = new SymfonyStyle($input, $output);
		$helper = $this->getHelper('question');

		// 1. Question for the model name with PascalCase validation
		$model_name_question = new Question('Enter the name of the model (PascalCase): ');
		$model_name_question->setValidator(function($answer){

			if(!preg_match('/^[A-Z][a-zA-Z0-9_]*$/', $answer))
			{
				throw new \RuntimeException('The model name must be in PascalCase with underscores (_) allowed.');
			}

			if(file_exists(APP_PATH."/model/{$answer}.php"))
			{
				$model_file = APP_PATH."/model/{$answer}.php";
				throw new \RuntimeException("Model {$model_file} already exists.");
			}

			return $answer;
		});
		$model_name = $helper->ask($input, $output, $model_name_question);

		// 2. Question for the table name with autocompletion from database tables
		$model_table_question = new Question('Enter the name of your table: ');
		$model_table_question->setValidator(function($answer){

			global $DB;

			$tables = $DB->query("SHOW TABLES")->fetchAllOne();
			if(!in_array($answer, $tables))
			{
				throw new \RuntimeException("Table {$answer} does not exist.");
			}

			return $answer;
		});
		$model_table = $helper->ask($input, $output, $model_table_question);

		// dir
		$model_dir = APP_PATH.'/model';
		if(!is_dir($model_dir))
		{
			mkdir($model_dir, 0755, true);
			$output->writeln("Created directory: {$model_dir}");
		}


		// create file
		$model_file = APP_PATH."/model/{$model_name}.php";

		$contents = file_get_contents(__DIR__.'/entity.tpl.php');
		$contents = str_replace('entity_name', $model_name, $contents);
		$contents = str_replace('timestamp_sql', now(), $contents);
		$contents = str_replace('table_name', $model_table, $contents);

		if(@!file_put_contents($model_file, $contents))
		{
			$output->writeln("<error>File `{$model_name}.php` can't be created.</error>");
			return Command::FAILURE;
		}

		$output->writeln("<info>File `/model/{$model_name}.php` created</info>");
		return Command::SUCCESS;
	}


}