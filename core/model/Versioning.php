<?php

namespace Model;

class Versioning extends \Core\Entity
{
	public static string $table = 'xcore_versioning';
	public static int $level_max = 10;


	public static function add(string $application, string $table, int $record_id, array $values, int $xcore_user_id)
	{
		// max level
		$version_ids = \Model\Versioning::allOne("application = :application and record_id = :record_id", ["application" => $application, "record_id" => $record_id], "id", "id desc", self::$level_max);

		// remove old records
		if(count($version_ids) == self::$level_max)
		{
			$last_version_id = end($version_ids);
			$sql = "delete from xcore_versioning where application = :application and record_id = :record_id and id <= {$last_version_id}";
			Db()->query($sql, ['application' => $application, 'record_id' => $record_id]);
		}

		// insert
		$row = [];
		$row['application'] = $application;
		$row['date'] = now();
		$row['table'] = $table;
		$row['record_id'] = $record_id;
		$row['xcore_user_id'] = $xcore_user_id;
		$row['data_json'] = json_encode($values);

		self::insert($row);

	}




}