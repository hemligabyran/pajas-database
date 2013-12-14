<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'default_driver' => 'database',

	'drivers' => array(
		'cookie' => array(

			// Encrypt the session data
			'encrypted' => FALSE,
		),
		'database' => array(

			// Cookie name
			'name' => 'session',

			// Cookie lifetime (integer)
			// 0 == just for the current session
			'lifetime' => 0,

			// Encrypt the session data
			'encrypted' => FALSE,
		),
	)
);