<?php

namespace Plugin\Core_Backend;

use Core\Debugbar;
use Core\Html;

class MailinglistController extends \Core\Controller {

	public string $table = 'xcore_mailinglist';
	public string $object_label = 'list';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {

		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 100);
		$datagrid->qSelectEmbedCount('xcore_mailinglist_subscriber', 'count');

		// search
		$datagrid->searchAddNumber('id');
		$datagrid->searchAddText('name');


		// columns
		$datagrid->addColumn('id', '', true);
		$datagrid->addColumnHtml('name', '', false, '');
		$datagrid->addColumnButton('count', 'email', '/@backend/mailinglist-subscriber/?search[]=xcore_mailinglist_id||[ID]', false, 'bi bi-envelope');
		$datagrid->addColumnHtml('export', '', false, 'min center');


		$datagrid->setOrderByInit('id', 'asc');


		// hookData
		$datagrid->hookData(function($row){

			$row['name'] = ucfirst($row['name']);
			$row['description'] = ucfirst($row['description']);
			$row['name'] = "<b>{$row['name']}</b><br>{$row['description']}";

			// export
			$row['export'] = Html::A(Html::Icon("bi bi-arrow-down-circle-fill"), "download/{$row['id']}/", ['class' => 'btn-mailinglist-export']);


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

		$form->addText('name', '', true, ['class' => 'ucfirst']);
		$form->addText('description', '', false, ['class' => 'ucfirst']);


		$form->addTextarea('import', '', false, ['placeholder' => 'please paste format: email; language; lastname; firstname;']);


		// validation
		if($form->isSubmitted())
		{
			// valid
			if($form->isValid())
			{
				$import = post('import');
				$emails_import = [];
				if(!empty($import))
				{
					$import = str_replace("\t", ";", $import);
					$lines = explode("\n", $import);

					for($i=0; $i < count($lines); $i++)
					{
						$cur_line = ($i + 1);

						$line = trim($lines[$i]);
						$cols = explode(';', $line);

						$email = trim(strtolower($cols[0]));

						if(!isset($cols[1]))$cols[1] = \Core\Config::get('frontend/langs')[0][0];
						$language = trim($cols[1]);

						if(!isset($cols[2]))$cols[2] = '';
						$lastname = trim($cols[2]) ?? '';

						if(!isset($cols[3]))$cols[3] = '';
						$firstname = trim($cols[3]) ?? '';

						// check email
						if(!isEmail($email))
						{
							$form->addError("Line {$cur_line}: invalid email address");
						}
						else
						{
							$emails_import[] = ['email' => $email, 'language' => $language, 'lastname' => $lastname, 'firstname' => $firstname];
						}

					}
				}

				$added = [];
				$form->save(['import'], $added);

				// import emails
				if(count($emails_import) > 0)
				{
					foreach($emails_import as $rec)
					{
						$sql = "select count(*) from xcore_mailinglist_subscriber where deleted = 'no' and xcore_mailinglist_id = :mailinglist_id and email = :email limit 1";
						$email_exists = DB()->query($sql, [':mailinglist_id' => $id, ':email' => $rec['email']])->fetchOne();
						if(!$email_exists)
						{
							$rec['xcore_mailinglist_id'] = $form->id;
							$rec['date'] = now();
							DB('xcore_mailinglist_subscriber')->insert($rec);
						}
					}
				}
			}

			return $form->json();
		}

		return $form->render();
	}


	/**
	 * @route /@backend/@module/download/{id}/
	 */
	public function download(int $id)
	{
		$sql = "select id,date,language,email,firstname,lastname from xcore_mailinglist_subscriber where deleted = 'no' and xcore_mailinglist_id=:mailing_id order by id";
		$rows = DB()->query($sql, [':mailing_id' => $id])->fetchAll();

		$contents = "id;date;language;email;firstname;lastname\n";
		foreach($rows as $row)
		{
			foreach($row as $key => $value)
				$contents .= "{$value};";
			$contents .= "\n";
		}

		// output
		Debugbar::disable();
		header("Content-Transfer-Encoding: UTF-8");
		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=mailing-list-{$id}.csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		return new \Core\Response($contents);
	}


}
