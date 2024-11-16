<?php
/**
 * convert csv to array
 *
 * @param string $file_path
 * @param array $headers = []
 * @param string $column_separator ','
 * @param bool $remove_enclosed_quotes true
 * @param bool $ignore_first false
 * @return array
 */
function csv2array(string $file_path, array $headers=[], string $column_separator=',', bool $remove_enclosed_quotes=true, bool $ignore_first=false): array
{

	// check if file is readable
	if(!file_exists($file_path) || !is_readable($file_path)) {
		throw new InvalidArgumentException("The file does not exist or is not readable: `{$file_path}`");
	}


	$contents = file_get_contents($file_path);
	$contents = trim($contents);
	if(empty($contents)) return [];

	$rows = explode("\n", $contents);

	if(!count($headers)) $ignore_first = true;

	$init = false;
	$lines = [];
	foreach($rows as $row)
	{
		$cols = explode($column_separator, trim($row));
		$cols = array_map('trim', $cols);
		if(!$init && $ignore_first)
		{
			$col_index = 0;
			foreach($cols as $col)
			{
				$cols[$col_index] = str_replace('"', "", $col);
				$col_index++;
			}

			$headers = $cols;
			$init = true;
			continue;
		}


		$row2 = [];
		$col_index = 0;
		foreach($headers as $header)
		{
			if(!isset($cols[$col_index]))$cols[$col_index] = '';

			if($remove_enclosed_quotes)
			{
				if($cols[$col_index][0] == '"')$cols[$col_index][0] = '';
				if($cols[$col_index][-1] == '"')$cols[$col_index][-1] = '';
				$cols[$col_index] = trim($cols[$col_index]);
			}

			$row2[$header] = $cols[$col_index];
		}

		$lines[] = $row2;
		$init = true;
	}




	return $lines;
}


