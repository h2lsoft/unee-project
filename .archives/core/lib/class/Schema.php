<?php

namespace Core;

class Schema
{
	private static array $stack = [];
	private static string $create_last_column = "";

	public static function createTable(string $table){

		$tmp = [
			'operation' => 'CREATE',
			'name' => $table,
			'engine' => \Core\Config::get('db/schema')['default_engine'],
			'charset' => \Core\Config::get('db/schema')['default_charset'],
			'collate' => \Core\Config::get('db/schema')['default_collate'],
			'auto_increment' => \Core\Config::get('db/schema')['default_auto_increment'],
			'columns' => [],
			'indexes' => [],
			'extra' => "",
			'comment' => "",
			'ifNotExists' => false,
			'softColumns' => true,
		];
		self::$stack[] = $tmp;

		return new \Core\Schema();
	}
	public static function alterTable(string $table){

		$tmp = [
			'operation' => 'ALTER',
			'name' => $table,
			'columns' => [],
			'indexes' => [],
			'extra' => "",
			'comment' => "",
			'ifNotExists' => false,
			'softColumns' => true,
		];
		self::$stack[] = $tmp;
		return new \Core\Schema();
	}

	// table properties
	private function setProperty(string $col, mixed $val){
		self::$stack[count(self::$stack)-1][$col] = $val;
	}
	private function addProperties(string $col, array $val){

		self::$stack[count(self::$stack)-1][$col][] = $val;
		if($col == 'columns')self::$create_last_column = $val['name'];

	}

	public function softColumns(bool $softColumns){$this->setProperty('softColumns', $softColumns); return $this;}
	public function engine(string $engine){$this->setProperty('engine', $engine); return $this;}
	public function collation(string $collation){$this->setProperty('collation', $collation); return $this;}
	public function comment(string $comment){$this->setProperty('comment', $comment); return $this;}
	public function extra(string $extra){$this->setProperty('extra', $extra); return $this;}
	public function autoincrement(int $autoincrement){$this->setProperty('autoincrement', $autoincrement); return $this;}
	public function ifNotExists(){$this->setProperty('ifNotExists', true); return $this;}

	// table columns
	public function addColumn(string $name, string $raw)
	{
		$col = [
			'name' => $name,
			'type' => 'RAW',
			'raw' => $raw,
		];

		$this->addProperties('columns', $col);
		return $this;
	}

	// numbers
	public function addInt(string $name, bool $unsigned=false, int $default=0, bool $null=false, string $extra="")
	{
		$col = [
			'name' => $name,
			'type' => 'INT',
			'null' => $null,
			'unsigned' => $unsigned,
			'extra' => $extra,
			'default' => $default,
		];

		$this->addProperties('columns', $col);
		return $this;
	}
	public function addBigInt(string $name, bool $unsigned=false, int $default=0, bool $null=false, string $extra="")
	{
		$col = [
			'name' => $name,
			'type' => 'BIGINT',
			'null' => $null,
			'unsigned' => $unsigned,
			'extra' => $extra,
			'default' => $default,
		];

		$this->addProperties('columns', $col);
		return $this;
	}
	public function decimal(string $name, int $integer_length,  int $decimal_length, bool $unsigned=false, int $default=0, bool $null=true, string $extra="")
	{
		$col = [
			'name' => $name,
			'type' => 'DECIMAL',
			'integer_length' => $integer_length,
			'decimal_length' => $decimal_length,
			'null' => $null,
			'unsigned' => $unsigned,
			'extra' => $extra,
			'default' => $default,
		];

		$this->addProperties('columns', $col);
		return $this;
	}

	// char
	public function addChar(string $name, int $length=10, bool $null=false, string $default='NULL', string $charset="", string $extra=""){

		$col = [
			'name' => $name,
			'type' => 'CHAR',
			'length' => $length,
			'default' => $default,
			'null' => $null,
			'charset' => $charset,
			'extra' => $extra,
		];

		$this->addProperties('columns', $col);
		return $this;
	}
	public function addVarchar(string $name, bool $null=false,  string $default='NULL', int $length=255, string $charset="", string $extra=""){

		$col = [
			'name' => $name,
			'type' => 'VARCHAR',
			'length' => $length,
			'null' => $null,
			'default' => $default,
			'charset' => $charset,
			'extra' => $extra,
		];

		$this->addProperties('columns', $col);
		return $this;
	}
	public function addText(string $name, string $charset="", string $extra=""){

		$col = [
			'name' => $name,
			'type' => 'TEXT',
			'null' => false,
			'charset' => $charset,
			'extra' => $extra,
		];

		$this->addProperties('columns', $col);
		return $this;
	}
	public function addLongtext(string $name, string $charset="", string $extra=""){

		$col = [
			'name' => $name,
			'type' => 'LONGTEXT',
			'null' => false,
			'charset' => $charset,
			'extra' => $extra,
		];

		$this->addProperties('columns', $col);
		return $this;
	}
	public function addBlob(string $name, string $charset="", string $extra=""){

		$col = [
			'name' => $name,
			'type' => 'BLOB',
			'null' => false,
			'charset' => $charset,
			'extra' => $extra,
		];

		$this->addProperties('columns', $col);
		return $this;
	}

	public function addEnum(string $name, array $values=[], string $default="", bool $null=false, string $charset="", string $extra=""){

		$col = [
			'name' => $name,
			'type' => 'ENUM',
			'values' => $values,
			'null' => $null,
			'default' => $default,
			'charset' => $charset,
			'extra' => $extra,
		];

		$this->addProperties('columns', $col);
		return $this;
	}
	public function addBoolean(string $name, string $extra=""){

		$col = [
			'name' => $name,
			'type' => 'ENUM',
			'values' => ['yes', 'no'],
			'null' => false,
			'default' => 'yes',
			'extra' => $extra,
		];

		$this->addProperties('columns', $col);
		return $this;
	}

	public function addBooleanX(string $name, string $extra=""){

		$col = [
			'name' => $name,
			'type' => 'ENUM',
			'values' => ['yes', 'no'],
			'null' => false,
			'default' => 'no',
			'extra' => $extra,
		];

		$this->addProperties('columns', $col);
		return $this;
	}

	// date
	public function addDate(string $name, bool $null=false, string $extra=""){

		$col = [
			'name' => $name,
			'type' => 'DATE',
			'null' => $null,
			'extra' => $extra,
		];

		$this->addProperties('columns', $col);
		return $this;
	}
	public function addDateTime(string $name, bool $null=false, string $extra=""){

		$col = [
			'name' => $name,
			'type' => 'DATETIME',
			'null' => $null,
			'extra' => $extra,
		];

		$this->addProperties('columns', $col);
		return $this;
	}

	// index
	public function addIndex(string $name="", int $length=0)
	{
		if(empty($name))$name = self::$create_last_column;

		$index = [
					'type' => 'INDEX',
					'name' => $name,
					'length' => $length,
		];

		$this->addProperties('indexes', $index);
		return $this;
	}
	public function addIndexUnique(string $name="")
	{
		if(empty($name))$name = self::$create_last_column;

		$index = [
			'type' => 'UNIQUE',
			'name' => $name,
		];

		$this->addProperties('indexes', $index);
		return $this;
	}
	public function addIndexFullText(string $name="")
	{
		if(empty($name))$name = self::$create_last_column;

		$index = [
					'type' => 'FULLTEXT',
					'name' => $name,
		];

		$this->addProperties('indexes', $index);
		return $this;
	}
	public function addIndexRaw(string $raw)
	{
		$index = [
			'type' => 'RAW',
			'raw' => $raw,
		];

		$this->addProperties('indexes', $index);
		return $this;
	}

	// raw
	public function addRaw(string $sql)
	{
		self::$stack[] = [
			'operation' => 'SQL',
			'sql' => $sql,
		];
		return $this;
	}

	// @todo> alter table
	// @todo> dropIndex



	// dropTableifExists
	public function dropTable(string $table, bool $if_exists=true)
	{
		$exists = ($if_exists) ? " IF EXISTS " : "";
		self::addRaw("DROP TABLE {$exists} `{$table}`");
		return $this;
	}

	// rename table
	public function renameTable(string $table, string $new_name)
	{
		self::addRaw("RENAME TABLE `{$table}` TO `{$new_name}`");
		return $this;
	}


	public function sql(string $sql){
		$tmp = [
			'operation' => 'SQL',
			'sql' => $sql
		];
		self::$stack[] = $tmp;
	}


	// output
	public function getSQLs():array {
		$sqls = [];

		foreach(self::$stack as $ins)
		{
			// create operation
			if($ins['operation'] == 'CREATE')
			{
				$if_not_exists = (!$ins['ifNotExists']) ? ""  : "IF NOT EXISTS";

				$sql = "CREATE TABLE `{$ins['name']}` {$if_not_exists}\n";
				$sql .= "(\n";

				$sql .= "    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,\n";

				$cols = '';
				foreach($ins['columns'] as $col)
				{
					if(!empty($cols))$cols .= ",\n";

					$cols .= "    `{$col['name']}` ";

					if($col['type'] == 'RAW')
					{
						$cols .= " {$col['raw']}";
					}
					else
					{
						if($col['type'] == 'VARCHAR')
							$cols .= " VARCHAR({$col['length']})";
						elseif($col['type'] == 'ENUM')
							$cols .= " ENUM('".join("', '", $col['values'])."')";
						else
							$cols .= " {$col['type']}";


						if(isset($col['null']) && !$col['null'])$cols .= " NOT";
						$cols .= " NULL";

						if(isset($col['default']))
						{
							if(!$col['null'] && $col['default'] == 'NULL')$col['default'] = '';
							if($col['default'] != 'NULL')$col['default'] = "'".addslashes($col['default'])."'";
							$cols .= " DEFAULT {$col['default']}";
						}
					}

					if(isset($col['charset']) && !empty($col['charset']))
						$cols .= " COLLATE '{$col['charset']}'";
				}



				$sql .= $cols;

				$sql .= ",\n    PRIMARY KEY (`id`),";
				if($ins['softColumns'])
				{
					$sql .= "\n";
					$sql .= "    `deleted` ENUM('yes','no') NOT NULL DEFAULT 'no',\n";
					$sql .= "    `created_at` DATETIME NULL DEFAULT NULL,\n";
					$sql .= "    `created_by` VARCHAR(255) NULL DEFAULT NULL,\n";
					$sql .= "    `updated_at` DATETIME NULL DEFAULT NULL,\n";
					$sql .= "    `updated_by` VARCHAR(255) NULL DEFAULT NULL,\n";
					$sql .= "    `deleted_at` DATETIME NULL DEFAULT NULL,\n";
					$sql .= "    `deleted_by` VARCHAR(255) NULL DEFAULT NULL,\n";
					$sql .= "    INDEX `deleted` (`deleted`)";
				}

				$indexes = "";
				foreach($ins['indexes'] as $index)
				{
					$sql .= ",\n    ";

					if($index['type'] == 'RAW')
					{
						$sql .= $index['raw'];
					}
					else
					{
						if($index['type'] == 'UNIQUE') $sql .= "UNIQUE ";
						if($index['type'] == 'FULLTEXT') $sql .= "FULLTEXT ";
						$sql .= "INDEX `{$index['name']}` (`{$index['name']}`)";
					}


				}

				$sql .= "\n) \n";

				if(!empty($ins['engine']))
					$sql .= "ENGINE={$ins['engine']}\n";

				if(!empty($ins['charset']))
					$sql .= " DEFAULT CHARSET='{$ins['charset']}'\n";

				if(!empty($ins['collate']))
					$sql .= "COLLATE='{$ins['collate']}'\n";

				if(!empty($ins['auto_increment']))
					$sql .= "AUTO_INCREMENT={$ins['auto_increment']}\n";

				if(!empty($ins['comment']))
					$sql .= "COMMENT='".addslashes($ins['comment'])."'\n";

				$sqls[] = $sql;
			}
			// create operation
			if($ins['operation'] == 'SQL')
			{
				$sqls[] = $ins['sql'];
			}

		}

		return $sqls;
	}

	public function execute()
	{
		$sqls = self::getSQLs();

		foreach($sqls as $sql)
			DB()->query($sql);

		// reset
		self::$stack = [];
		self::$create_last_column = "";
	}


}


