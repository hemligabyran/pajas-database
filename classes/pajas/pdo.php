<?php defined('SYSPATH') or die('No direct script access.');

class Pajas_Pdo
{

	/**
	 * Saved instances of this object for later fetching
	 *
	 * @var array of objects
	 */
	public static $instances = array();

	/**
	 * PDO connection
	 *
	 * @var object
	 */
	public $db;

	public function __construct($instance_name = 'default')
	{
		if (( ! isset(self::$instances[$instance_name])) && ($db_settings = Kohana::$config->load('pdo.'.$instance_name)))
		{
			$connection_string = Kohana::$config->load('pdo.connection_strings.'.$db_settings['driver']);
			foreach ($db_settings as $key => $value)
				$connection_string = str_replace('{'.$key.'}', $value, $connection_string);

			if (isset($db_settings['username']) && isset($db_settings['password']))
				$this->db = new Pajas_Pdoextended($connection_string, $db_settings['username'], $db_settings['password']);
			else
				$this->db = new Pajas_Pdoextended($connection_string);

		  self::$instances[$instance_name] = $this;

			if ($initial_query = Kohana::$config->load('pdo.initial_query.'.$db_settings['driver']))
				$this->db->query($initial_query);

			if (Kohana::$environment === Kohana::DEVELOPMENT)
				$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
	}

	public static function instance($instance_name = 'default')
	{
		if ( ! isset(self::$instances[$instance_name])) new self($instance_name);
		if (isset(self::$instances[$instance_name]))    return self::$instances[$instance_name]->db;

		return FALSE;
	}

}