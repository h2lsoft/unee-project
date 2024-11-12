<?php

namespace Command\App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class Install extends Command {

	protected function configure(): void
	{
		$this->setName("install");
		$this->setDescription("Unee installer");
		$this->setHelp('This command allows you to install Unee app');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		global $DB, $CONFIG;

		// return this if some error happened during the execution => return Command::FAILURE;
		// return this to indicate incorrect command usage; e.g. invalid options or missing arguments (it's equivalent to returning int(2)) => return Command::INVALID
		$helper = $this->getHelper('question');
		$question = new ConfirmationQuestion("<question>Would you like to install Unee (default = yes) ?</question> ", true);
		if(!$helper->ask($input, $output, $question))
			return Command::SUCCESS;

		// user Email
		$question = new Question('<question>Please enter your email :</question> ', '');
		$question->setValidator(function (string $answer): string {
			if (!is_string($answer) || !(filter_var($answer, FILTER_VALIDATE_EMAIL)) ) {
				throw new \RuntimeException(
					"Please enter a valid email"
				);
			}

			return $answer;
		});

		$question->setMaxAttempts(3);
		$INPUT_EMAIL = $helper->ask($input, $output, $question);

		// queries
		$output->writeln("<comment>Loading queries...</comment>");
		$sqls = file_get_contents('core/command/app/install.sql');

		$sqls = str_replace("=@DB_ENGINE", '='.$CONFIG['db']['schema']['default_engine'], $sqls);
		$sqls = str_replace("=@DB_CHARSET", '='.$CONFIG['db']['schema']['default_charset'], $sqls);
		$sqls = str_replace("=@DB_COLLATE", '='.$CONFIG['db']['schema']['default_collate'], $sqls);


		$sqls = str_replace("\r\n", "\n", $sqls);
		$sqls = explode(";\n", $sqls);
		foreach($sqls as $sql)
		{
			$sql = trim($sql);
			if(empty($sql))continue;
			$DB->query($sql);
		}

		// migration
		$output->writeln("<comment>Create migration...</comment>");
		$DB->query("INSERT INTO xcore_migration (version) VALUES ('1.0.0')");

		// user profile
		$output->writeln("<comment>Create use profile...</comment>");
		$DB->table('xcore_user')->insert([
									'id' => 1,
									'xcore_group_id'  => 1,
									'language'  => 'en',
									'lastname'  => 'super',
									'firstname'  => 'admin',
									'email'  => $INPUT_EMAIL,
									'login'  => 'superadmin'
		]);

		// generate password
		$password = bin2hex(openssl_random_pseudo_bytes(6));
		$password_hashed = password_hash($password, $CONFIG['auth']['security']['password']['algo'], $CONFIG['auth']['security']['password']['algo_options']);
		$DB->query("UPDATE xcore_user SET password = :password_hashed", [':password_hashed' => $password_hashed]);



		// finish
		$url = $CONFIG['url']."/{$CONFIG['backend']['dirname']}/";
		$output->writeln("---");
		$output->writeln("<info>Please sign in and configure your profile => {$url}</info>");
		$output->writeln("<info> - Login : superadmin</info>");
		$output->writeln("<info> - Password : {$password}</info>");
		$output->writeln("<info> - Email : {$INPUT_EMAIL}</info>\n");

		return Command::SUCCESS;
	}


}
