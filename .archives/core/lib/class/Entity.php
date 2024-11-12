<?php

namespace Core;

class Entity
{
	static public string $table = '';
	static public string $fields = "*";
	static public string $where_added = "";
	
	
	/**
	 * insert record
	 *
	 * @param array $row
	 * @param string $created_by
	 *
	 * @return int id of new record
	 */
	public static function insert(array $row, string $created_by=""):int
	{
		return DB(static::$table)->insert($row, $created_by);
	}
	
	/**
	 * update record
	 *
	 * @param array  $row
	 * @param int|array  $where
	 * @param int    $limit
	 * @param string $updated_by
	 *
	 * @return int number of updated
	 */
	public static function update(array $row, int|array $where=[], int $limit=-1, string $updated_by=''):int
	{
		return DB(static::$table)->update($row, $where, $limit, $updated_by);
	}
	
	/**
	 * @param array     $row
	 * @param array|string     $where
	 * @param array     $exclude_from_update
	 * @param string    $by
	 *
	 * @return int
	 */
	public static function replace(array $row, array|string $where=[], array $exclude_from_update=[], string $by=''):int
	{
		$params = [];
		if(is_array($where))
		{
			$params = $where[1];
			$where = $where[0];
		}
		
		$rec = self::findOne($where, $params);
		if($rec)
		{
			$row2 = [];
			foreach($row as $key => $val)
			{
				if(!in_array($key, $exclude_from_update))
					$row2[$key] = $val;
			}
			return DB(static::$table)->update($row2, $where, 1, $by);
		}
		else
		{
			foreach($row as $key => $val)
			{
				if($key != 'id')
				{
					$row[$key] = $val;
				}
			}
			
			return DB(static::$table)->insert($row, $by);
		}
		
		
	}
	
	
	
	/**
	 * delete record
	 *
	 * @param int    $id
	 * @param string $deleted_by
	 *
	 * @return int number of deleted
	 */
	public static function delete(int|string|array $id_or_where, string $deleted_by=''):int
	{
		return DB(static::$table)->delete($id_or_where, -1, $deleted_by);
	}
	
	/**
	 * get record by id
	 *
	 * @param int    $id
	 * @param string $fields
	 *
	 * @return false|array
	 */
	public static function findByID(int $id, string $fields=''):false|array
	{
		if(empty($fields))$fields = static::$fields;
		$record = self::findOne("id = {$id}", [], $fields, );
		return $record;
	}
	
	/**
	 * get one record
	 *
	 * @param string|array $where
	 * @param array  $params
	 * @param string $fields
	 * @param array  $links
	 * @param string $order_by
	 * @param int $limit 1
	 *
	 * @return false|array
	 */
	public static function findOne(string $where, array $params=[], string $fields='', array $links=[], string $order_by=''):false|array
	{
		if(empty($fields)) $fields = static::$fields;
		
		if(!empty(static::$where_added))
			$where = (empty($where)) ? static::$where_added : "{$where} AND ".static::$where_added;
		
		$obj = DB()->select($fields)->from(static::$table)->where($where)->limit(1);
		
		if(!empty($order_by))
			$obj->orderBy($order_by);
		
		$record = $obj->executeSQL($params)->fetch();
		
		return $record;
	}
	
	/**
	 * get multiple record
	 * @param string $where
	 * @param array  $params
	 * @param string $fields
	 * @param string $group_by
	 * @param string $order_by
	 * @param int    $limit
	 * @param array  $links
	 *
	 * @return array|bool
	 */
	public static function all(string $where="", array $params=[], string $fields='', string $group_by='', string $order_by='', int $limit=-1, array $links=[])
	{
		if(empty($fields))$fields = static::$fields;
		
		if(!empty(static::$where_added))
			$where = (empty($where)) ? static::$where_added : "{$where} AND ".static::$where_added;
		
		$obj = DB()->select($fields)->from(static::$table);
		
		if(!empty($where))$obj->where($where);
		if(!empty($order_by))$obj->orderBy($order_by);
		if(!empty($group_by))$obj->groupBy($group_by);
		if($limit != -1)$obj->limit($limit);
		
		$records = $obj->executeSQL($params)->fetchAll();
		
		return $records;
	}
	
	/**
	 * get records by one column
	 * @param string $where
	 * @param array  $params
	 * @param string $fields
	 * @param string $order_by
	 * @param int    $limit
	 *
	 * @return bool
	 */
	public static function allOne(string $where="", array $params=[], string $fields='id', string $order_by='', int $limit=-1)
	{
		if(empty($fields))$fields = static::$fields;
		
		if(!empty(static::$where_added))
			$where = (empty($where)) ? static::$where_added : "{$where} AND ".static::$where_added;
		
		$obj = DB()->select($fields)->from(static::$table)->where($where);
		if(!empty($order_by))$obj->orderBy($order_by);
		if($limit != -1)$obj->limit($limit);
		
		$records = $obj->executeSQL($params)->fetchAllOne();
		return $records;
		
	}
	
	/**
	 * get count
	 * @param string $conditions
	 * @param array  $binds
	 * @param string $count_field
	 *
	 * @return int
	 */
	public static function count(string $conditions, array $binds=[], string $count_field='*'):int
	{
		$table = static::$table;
		$sql = "SELECT count({$count_field}) FROM {$table} WHERE {$conditions} LIMIT 1";
		$count = DB()->query($sql, $binds)->fetchOne();
		
		return $count;
	}
	
	
	/**
	 * return id or false if record is not found
	 *
	 * @param string $conditions
	 * @param array  $binds
	 * @param int    $id_allowed
	 *
	 * @return bool|int
	 */
	public static function found(string $conditions, array $binds=[], int $id_allowed = 0): bool|int
	{
		if($id_allowed)
			$conditions .= " AND id != {$id_allowed}";
		
		$table = static::$table;
		$sql = "SELECT id FROM {$table} WHERE {$conditions} LIMIT 1";
		
		$found = DB()->query($sql, $binds)->fetch();
		if(is_array($found))$found = $found['id'];
		
		return $found;
	}

}