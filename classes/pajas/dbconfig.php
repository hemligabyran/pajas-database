<?php defined('SYSPATH') or die('No direct script access.');

abstract class Pajas_Dbconfig
{

	// Database instance
	public $pdo = 'default';

	protected static $instances = array();

	public function __construct($instance_name = NULL)
	{
		if ($instance_name !== NULL)
		{
			// Set the database instance name
			$this->pdo = $instance_name;
		}

		if (is_string($this->pdo))
		{
			$config_name = $this->pdo;
			// Load the database
			$this->pdo = Pajas_pdo::instance($this->pdo);
		}
		else throw new Exception('PDO instance name is not a string');


		if (Kohana::$environment === Kohana::DEVELOPMENT)
		{
			$sql = 'SELECT *
				FROM information_schema.tables
				WHERE TABLE_SCHEMA = '.$this->pdo->quote(Kohana::$config->load('pdo.'.$config_name.'.database_name')).' AND TABLE_NAME = \'dbconfig\';';

			if ($this->pdo->query($sql)->rowCount() == 0)
			{
				$this->pdo->exec('CREATE TABLE `dbconfig` (
						`property` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
						`value` text COLLATE utf8_unicode_ci NOT NULL,
						PRIMARY KEY (`property`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
			}
		}
	}

	public static function instance($instance_name = NULL)
	{
		if ( ! array_key_exists($instance_name, self::$instances))
		{
			$class_name = get_called_class();
			self::$instances[$instance_name] = new $class_name($instance_name);
		}

		return self::$instances[$instance_name];
	}

	public function get($property)
	{
		$sql = 'SELECT `value` FROM dbconfig WHERE property = '.$this->pdo->quote($property);

		return $this->pdo->query($sql)->fetchColumn();
	}

	public function set($property, $value)
	{
		if ((is_string($property) || is_numeric($property)) && (is_string($value) || is_numeric($value)))
		{
			if ($this->get($property) != $value)
			{
				Kohana::$log->add(LOG::INFO, 'Dbconfig updated. property='.$property.' value='.$value);
				$sql = 'REPLACE INTO dbconfig (property, `value`) VALUES('.$this->pdo->quote($property).','.$this->pdo->quote($value).');';

				return $this->pdo->exec($sql);
			}

			return TRUE; // Value is already set correctly
		}
		else
		{
			Kohana::$log->add(LOG::ERROR, 'Dbconfig not updated. Property or value is not strings');
		}
	}

}