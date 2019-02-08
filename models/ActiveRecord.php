<?php

abstract class ActiveRecord {

	protected static $mysqli = false;

	public function __construct($id = null)
	{
		$table = static::table();
		$fields = static::fields($table);

		foreach ($fields as $key=>$value) {
			if (!isset($this->{$key})){
				$this->{$key} = null;
			}
		}

		if ($id) {
			$this->read($id);
		}
	}

	public function set($data)
	{
		$table = static::table();
		$fields = static::fields($table);

		foreach ($data as $key=>$value) {
			if (isset($fields[$key])) {
				unset($this->{$key});
				$this->{$key} = $value;
			}
		}
	}

	public function save()
	{
		$table = static::table();
		$fields = static::fields($table);
		$primaryKey = static::primaryKey();

		if (!empty($this->{$primaryKey})) {

			$sql[] = 'UPDATE';

		} else {
		
			$sql[] = 'INSERT';

		}

		$sql[] = '`' . static::escape($table) . '` SET';

		foreach ($this as $key=>$value) {
		
			if (isset($fields[$key])) {

				if ($key == $primaryKey) {
					continue;
				}

				if (empty($value)) {

					$values[] = '`' . static::escape($key) . '` = NULL';

				} else {
				
					$values[] = '`' . static::escape($key) . '` = "' . static::escape($value) . '"';

				}

			}

		}

		if (empty($values)) {
			throw new Exception('Not values for save');
		}
		
		$sql[] = implode(', ', $values);

		if (!empty($this->{$primaryKey})) {

			$sql[] = 'WHERE `' . $primaryKey . '` = "' . static::escape($this->{$primaryKey}) . '"';

		}

		static::query(implode(' ', $sql));

		if (empty($this->{$primaryKey})) {
		
			$this->{$primaryKey} = self::$mysqli->insert_id;
		
		}

		return true;

	}

	public function read(int $id)
	{
		$table = static::table();
		$primaryKey = static::primaryKey();
		$sql = 'SELECT * FROM `' . $table . '` WHERE `' . $primaryKey . '` = ' . $id;
		$result = static::query($sql);
		$record = $result->fetch_object($table);
		$this->set($record);
	}

	public function delete()
	{
		$table = static::table();
		$primaryKey = static::primaryKey();
		$id = $this->{$primaryKey};
		if (!empty($id)) {
			$sql = 'DELETE FROM `' . $table . '` WHERE `' . $primaryKey . '` = ' . $id;
			static::query($sql);
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

	public static function objects($sql=false)
	{
		$table = static::table();
		$primaryKey = static::primaryKey();
		$records = [];

		if (!$sql) {
			$sql = 'SELECT * FROM ' . $table;
		}

		$result = static::query($sql);

		while ($record = $result->fetch_object($table)) {
			$id = $record->{$primaryKey};
			$records[$id] = $record;
		}

		return $records;

	}

	public static function query($sql)
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

	public static function escape($text)
	{
		$text = self::$mysqli->real_escape_string($text);
		return $text;
	}

	public static function fields()
	{
		$table = static::table();
		$sql = 'DESCRIBE `' . $table . '`';
		$result = static::query($sql);
		$fields = [];

		while ($record = $result->fetch_assoc()) {
			$name = $record['Field'];
			$fields[$name] = $record;
		}

		return $fields;
	}

	public static function table()
	{
		$table = get_called_class();
		return $table;
	}

	public static function primaryKey()
	{
		$table = static::table();
		$sql = 'SHOW KEYS FROM ' . $table . ' WHERE Key_name = "PRIMARY"';
		$result = self::query($sql);
		$record = $result->fetch_assoc();
		$primaryKey = $record['Column_name'];

		return $primaryKey;
	}

}
