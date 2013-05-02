<?php defined('SYSPATH') or die('No direct script access.');

class Pajas_Pdoextended extends PDO
{

	public function quote($value, $parameter_type = PDO::PARAM_STR)
	{
		if     (is_null($value)) return 'NULL';
		elseif (is_int($value))  return $value;
		else   return parent::quote($value, $parameter_type);
	}

}