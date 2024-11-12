<?php

namespace Plugin\Core_Backend;

use \Model\Cron;
use Symfony\Component\HttpFoundation\Response;

class CronController extends \Core\Controller {
	
	public string $table = 'xcore_crontask';
	public string $object_label = 'task';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {
		
		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 25);
		
		// search
		$datagrid->searchAddNumber('id');
		$datagrid->searchAddSelectSql('category');
		$datagrid->searchAddText('name');
		$datagrid->searchAddSelectSql('status');
		$datagrid->searchAddBoolean('locked');
		$datagrid->searchAddBoolean('active');
		
		// columns
		$datagrid->addColumn('priority', '', true, 'min center');
		$datagrid->addColumn('id', '', true, 'min center');
		$datagrid->addColumn('category', '', true, 'min');
		$datagrid->addColumnHtml('name', '', false, '');
		$datagrid->addColumnHtml('repetition', '', false, 'min');
		$datagrid->addColumnHtml('status', '', false, 'min center');
		$datagrid->addColumnHtml('last_executed', 'last execution', false, 'min text-end');
		$datagrid->addColumnNote('last_error_message', 'last error', false, "bi bi-exclamation-triangle-fill", "text-danger");
		
		$datagrid->addColumnBoolean('active');
		$datagrid->addColumnHtml('locked', '', false, 'min center');
		$datagrid->addColumn('locked_date', 'locked date');
		
		$datagrid->setOrderByInit('priority', 'asc');
		
		
		
		// hookData
		$datagrid->hookData(function($row){
			
			$row['repetition'] = "Months : {$row['repetition_months']}<br>Days : {$row['repetition_days']}<br>Hours : {$row['repetition_hours']}<br>Minutes : {$row['repetition_minutes']}";
			
			
			$badge = '';
			if($row['status'] == "")$row['status'] = "idle";
			if($row['status'] == "in course")$badge = 'warning';
			
			
			$row['status'] = \Core\Html::Badge($row['status'], $badge);
			
			if($row['locked'] == "no")
			{
				$row['locked'] = "";
				$row['locked_date'] = "";
			}
			else
			{
				$row['locked'] = \Core\Html::Icon("bib bi-lock-fill text-warning");
			}
			
			if($row['last_execution_date_start'] == '0000-00-00 00:00:00')$row['last_execution_date_start'] = '-';
			if($row['last_execution_date_end'] == '0000-00-00 00:00:00')$row['last_execution_date_end'] = '-';
			
			$row['last_executed'] = "start : {$row['last_execution_date_start']}<br>ends : {$row['last_execution_date_end']}";
			
			
			$row['last_error_message'] = nl2br($row['last_error_message']);
			
			return $row;
		});
		
		
		$data = [];
		$data['content'] = $datagrid->render();
		return View('@plugin-content', $data);
	}

	/**
	 * @route /@backend/@module/add/ {method:"GET|POST", controller:"add"}
	 * @route /@backend/@module/edit/{id}/ {method:"GET|PUT", controller:"edit"}
	 */
	public function getForm(int $id=0)
	{
		$form = new \Component\Form();
		$form->linkController($this, $id);
		
		$form->addText('category', '', true, ['class' => 'ucfirst'])->datalist();
		$form->addText('name', '', true, ['class' => 'ucfirst']);
		$form->addText('script_path', 'script path', true);
		$form->addTextarea('script_parameters', 'script parameters', false)->setHelp("one parameter by line, ex: `param_name = param_value`");
		$form->addText('description', '', false, ['class' => 'ucfirst']);
		
		
		$form->addHeader('repetition');
		$form->addText('repetition_months', 'months', true)->setHelp("put * for all or comma separated for multiple values");
		$form->addText('repetition_days', 'days', true)->setHelp("put * for all or comma separated for multiple values");
		$form->addText('repetition_hours', 'hours', true)->setHelp("put * for all or comma separated for multiple values");
		$form->addText('repetition_minutes', 'minutes', true)->setHelp("put * for all or comma separated for multiple values");
		
		$form->addHeader('report');
		$form->addSwitch('email_report', 'Email report', false)->setValue('no');
		$form->addText('email_report_recipients', 'recipients', false)->setHelp("comma separated or semi-columns for multiple values");
		$form->addText('email_report_subject', 'subject', false);
		$form->addSwitch('email_report_failure', 'only on failure', false);
		$form->addHr();
		
		$form->addNumber('priority', '', true, ["class" => 'text-center'])->setInputSize(1);
		$form->addSwitch('active', '', false)->setValue('yes');
		$form->addSwitch('locked', '', false);
		
		
		// validation
		if($form->isSubmitted())
		{
			if($form->inputGet('email_report') == 'yes')
			{
				$form->input('email_report_recipients', 'recipients')->required();
			}
			
			
			// valid
			if($form->isValid())
			{
				$form->save();
			}
			
			return $form->json();
		}
		
		
		return $form->render();
		
	}

	/**
	 * @route /cron/
	 * @route /cron/{id}/
	 */
	public function execute(int $id=0)
	{
		if(!APP_CLI_MODE)die("Forbidden, please run in cli mode");
		
		
		register_shutdown_function(['\Model\Cron', 'shutdownHandler']);
		
		$current_day = (int)date('d');
		$current_month = (int)date('m');
		$current_hour = (int)date('H');
		$current_minutes = date('i');
		
		if(!$id)
			$sql = "SELECT * FROM {$this->table} WHERE deleted = 'no' AND active = 'yes' AND locked = 'no' ORDER BY priority";
		else
			$sql = "SELECT * FROM {$this->table} WHERE deleted = 'no' AND id = {$id} AND locked = 'no'";
		
		$crons = DB()->query($sql)->fetchAll();
		
		// lock compatible crons
		$crons_compatible = [];
		foreach($crons as $cron)
		{
			$cron_repetition_months = explode(',', str_replace(';', ',', trim(str_erase(' ', $cron['repetition_months']))));
			$cron_repetition_days = explode(',', str_replace(';', ',', trim(str_erase(' ', $cron['repetition_days']))));
			$cron_repetition_hours = explode(',', str_replace(';', ',', trim(str_erase(' ', $cron['repetition_hours']))));
			$cron_repetition_minutes = explode(',', str_replace(';', ',', trim(str_erase(' ', $cron['repetition_minutes']))));
			
			if(
				$id ||
				(
					(trim($cron['repetition_months']) == '*' || in_array($current_month, $cron_repetition_months)) &&
					(trim($cron['repetition_days']) == '*' || in_array($current_day, $cron_repetition_days)) &&
					(trim($cron['repetition_hours']) == '*' || in_array($current_hour, $cron_repetition_hours)) &&
					(trim($cron['repetition_minutes']) == '*' || in_array($current_minutes, $cron_repetition_minutes))
				)
				
			)
			{
				$crons_compatible[] = $cron;
				
				// locked
				DB($this->table)->update([
					'locked' => 'yes',
					'locked_date' => now(),
					'status' => 'in course',
					'last_execution_date_start' => now(),
					'last_execution_date_end' => '',
					'last_error_message' => ''
				], $cron['id']);
			}
		}
		
		
		$crons = $crons_compatible;
		
		
		$task_executed = 0;
		$task_error = 0;
		foreach($crons as $cron)
		{
			\Model\Cron::$current = $cron;
			\Model\Cron::$current_status = 'in course';
			$cron_execution_time_start = microtime(1);
			
			\Model\Cron::$stack = [];
			\Model\Cron::write("Task #{$cron['id']}> start");
				
			$script_path = APP_PATH.'/'.ltrim($cron['script_path'], '/');
			
			if(!file_exists($script_path))
			{
				$task_error++;
				\Model\Cron::$current_status = 'error';
				\Model\Cron::write("script `$script_path` not exists", \Model\Cron::$current_status);
				\Model\Cron::update([
										'locked' => 'no',
										'status' => \Model\Cron::$current_status,
									  	'last_error_message' => \Model\Cron::$last_message,
									  	'last_execution_date_end' => now()
									 ], $cron['id']);
			}
			else
			{
				\Model\Cron::write("Script execution `{$cron['script_path']}`");
				include($script_path);
				\Model\Cron::$current_status = 'finish';
				\Model\Cron::update(['locked' => 'no', 'status' => \Model\Cron::$current_status, 'last_execution_date_end' => now(), 'last_error_message' => ""], $cron['id']);
				
				$task_executed++;
				\Model\Cron::write("Script execution finish");
				
			}
				
				
			$cron_execution_time_end = microtime(1);
			$cron_execution_time_seconds = (int)($cron_execution_time_end - $cron_execution_time_start);
			\Model\Cron::write("Task #{$cron['id']}> finish in {$cron_execution_time_seconds} seconds");
				
				
			// email report
			if($cron['email_report'] == 'yes' && ($cron['email_report_failure'] == 'no' || ($cron['email_report_failure'] == 'yes' && \Model\Cron::$current_status == 'error')))
			{
				$body = \Model\Cron::stackGetAll();
				\Model\Cron::write(" => Script email report to `{$cron['email_report_recipients']}`", 'info');
				
				$subject = $cron['email_report_subject'];
				if(empty($subject))
				{
					$last_status = \Model\Cron::$current_status;
					$subject = "[{$cron['name']}] Email report (status: {$last_status})";
				}
				
				$emails = explode(',', trim(str_replace(';', ',', trim($cron['email_report_recipients']))));
					
				foreach($emails as $to)
				{
					$to = trim($to);
					if(empty($to))continue;
					\Core\Mailer::send(\Core\Config::get('cron/report_email_from'), $to, $subject, $body);
				}
			}
		}
		
		
		// clear static
		\Model\Cron::$current = [];
		
		// output
		$task_total = $task_error + $task_executed;
		if(!$task_total)
		{
			\Model\Cron::write("no task found");
		}
		else
		{
			\Model\Cron::write("------------------------------------");
			\Model\Cron::write("Tasks error : {$task_error}");
			\Model\Cron::write("Tasks executed : {$task_executed}");
			\Model\Cron::write("Tasks total : {$task_total}");
		}
		
		return new Response("");
	}
	
}