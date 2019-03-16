<?php

abstract class ActiveRecord {

	protected static $mysqli = false;
	protected $id = null;

	public function __construct()
	{
		$fields = $this->fields();

		foreach ($fields as $key=>$value) {
			if (!isset($this->{$key})){
				$this->{$key} = null;
			}
		}
	}

	public static function objects($sql=false)
	{
		$table = get_called_class();
		$records = [];

		if (!$sql) {
			$sql = 'SELECT * FROM ' . $table;
		}

		$result = self::$mysqli->query($sql);

		while ($record = $result->fetch_object($table)) {
			$id = $record->id;
			$records[$id] = $record;
		}

		return $records;

	}

	public function fields()
	{
		$table = $this->table();
		$sql = 'DESCRIBE `' . $table . '`';
		$result = $this->query($sql);
		$fields = [];

		while ($record = $result->fetch_assoc()) {
			$name = $record['Field'];
			if ($name == 'id') {
				continue;
			}
			$fields[$name] = $record;
		}

		return $fields;
	}

	public function set($data)
	{
		foreach ($data as $key=>$value) {
			if ($key == 'id') {
				continue;
			}
			unset($this->{$key});
			$this->{$key} = $value;
		}
	}

	public function save()
	{
		$table = $this->table();
		$fields = $this->fields();

		if (!empty($this->id)) {

			$sql[] = 'UPDATE';

		} else {
		
			$sql[] = 'INSERT';

		}

		$sql[] = '`' . $this->escape($table) . '` SET';

		foreach ($this as $key=>$value) {
		
			if (isset($fields[$key])) {

				if ($key == 'id') {
					continue;
				}

				if (empty($value)) {

					$values[] = '`' . $this->escape($key) . '` = NULL';

				} else {
				
					$values[] = '`' . $this->escape($key) . '` = "' . $this->escape($value) . '"';

				}

			}

		}

		if (empty($values)) {
			throw new Exception('Not values for save');
		}
		
		$sql[] = implode(', ', $values);

		if (!empty($this->id)) {

			$sql[] = 'WHERE `id` = "' . $this->escape($this->id) . '"';

		}

		$this->query(implode(' ', $sql));

		if (empty($this->id)) {
		
			$this->id = self::$mysqli->insert_id;
		
		}

		$this->read($this->id);

		return true;

	}

	public function id()
	{
		return $this->id;
	}

	public function read(int $id)
	{
		if (!preg_match('/^[1-9][0-9]*$/', $id)) {
			throw new Exception('Wrong id format');
		}

		$table = $this->table();
		$sql = 'SELECT * FROM `' . $table . '` WHERE `id` = ' . $id;
		$result = $this->query($sql);
		if ($result->num_rows == 0) {
			throw new Exception("Record $id not found in table $table");
		}
		$record = $result->fetch_object($table);
		$this->set($record);
		$this->id = $record->id;
	}

	public function delete()
	{
		$table = $this->table();
		$id = $this->id;
		if (!empty($id)) {
			$sql = 'DELETE FROM `' . $table . '` WHERE `id` = ' . $id;
			$this->query($sql);
		}
	}

	public static function config($host, $user, $password, $database)
	{
		if (!self::$mysqli) {

			self::$mysqli = new \mysqli($host, $user, $password, $database);
			self::$mysqli->set_charset('utf8');

			if (self::$mysqli->connect_errno) {
				throw new Exception('Database connect error: ' . self::$mysqli->connect_error);
			}

		}

	}

	public function query($sql)
	{
		$result = self::$mysqli->query($sql);

		if ($result === false) {
			$error = self::$mysqli->error;
			$sql = preg_replace('/^\t*/m', ' ', $sql);
			$sql = preg_replace('/\R/', '', $sql);
			throw new Exception($error . ': ' . $sql);
		}

		return $result;
	}

	public function escape($text)
	{
		$text = self::$mysqli->real_escape_string($text);
		return $text;
	}

	public function table()
	{
		$table = get_called_class();
		return $table;
	}

}
