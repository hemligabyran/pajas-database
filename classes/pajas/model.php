<?php defined('SYSPATH') or die('No direct script access.');

abstract class Pajas_Model
{

	// Database instance
	public $pdo = 'default';
	
	// Data array
	protected $data;

	/**
	 * Loads the database.
	 *
	 *     $model = new Foo_Model($db);
	 *
	 * @param   mixed  Database instance object or string
	 * @return  void
	 */
	public function __construct($instance_name = NULL)
	{
		if ($instance_name !== NULL)
		{
			// Set the database instance name
			$this->pdo = $instance_name;
		}

		if (is_string($this->pdo))
		{
			// Load the database
			$this->pdo = Pajas_pdo::instance($this->pdo);
		}
	}

	public function __get($name=NULL)
	{
		if (is_array($this->data) && isset($this->data[$name]))
			return $this->data[$name];
		else
			return FALSE;
	}


	public function get()
	{
		if ($this->data != NULL)
		{
			return $this->data;
		}

		return array();
	}


	public static function factory($instance_name = NULL)
	{
		$class_name = get_called_class();
		return new $class_name($instance_name);
	}

}