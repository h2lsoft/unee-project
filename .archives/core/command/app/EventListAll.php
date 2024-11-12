<?php

namespace Command\App;

use Exception;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

class EventListAll extends Command {

	protected function configure(): void
	{
		$this->setName('event:list');
		$this->setDescription('list all @event in controller');
		$this->setHelp('This command allows you to install Unee app');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$table = new Table($output);

		$all_controllers = \Core\Controller::getAll();

		$events = [];
		$events[] = ['core.controller.init', ""];
		foreach($all_controllers as $c_controller)
		{
			$info = \Core\Controller::getInfo($c_controller);
			$class_path = $info['class_path'];

			$class = new ReflectionClass($class_path);
			$methods = $class->getMethods();

			foreach($methods as $method)
			{
				$doclet = $method->getDocComment();
				if($doclet)
				{
					preg_match_all("/@event(.*)$/m", $doclet, $matches);
					if(count($matches[1]))
					{
						foreach($matches[1] as $event_line)
						{
							$event_line = str_replace("\t", " ", $event_line);
							$event_line = trim($event_line);

							$tmp = explode(" ", $event_line, 2);
							$event_name = $tmp[0];
							$event_description = "";
							if(count($tmp) == 2)
							{
								$event_description = trim($tmp[1]);
							}

							$events[] = [$event_name, $event_description];
						}
					}
				}
			}
		}

		sort($events);
		$table->setHeaders(['Event', 'Description'])->setRows($events);
		$table->render();


		return Command::SUCCESS;
	}
}