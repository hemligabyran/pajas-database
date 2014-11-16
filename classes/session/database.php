<?php defined('SYSPATH') or die('No direct script access.');

class Session_Database extends Session
{

	// Session id
	protected $id;

	public function __construct(array $config = NULL, $id = NULL)
	{
		$this->pdo = Pajas_Pdo::instance();

		if (Kohana::$environment === Kohana::DEVELOPMENT)
		{
			if ($this->pdo->query('SHOW TABLES LIKE \'sessions\';')->rowCount() == 0)
			{
				$this->pdo->exec('CREATE TABLE sessions (
						id char(16) COLLATE utf8_unicode_ci NOT NULL,
						`data` longtext COLLATE utf8_unicode_ci NOT NULL,
						updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						PRIMARY KEY (id)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
					ALTER TABLE sessions ADD INDEX updated_idx (updated);');
			}
		}

		parent::__construct($config, $id);
	}

	/**
	 * @return  string
	 */
	public function id()
	{
		if ( ! $this->id)
			$this->id = Cookie::get($this->_name, NULL);

		return $this->id;
	}

	/**
	 * Set session ID
	 */
	public function set_id($new_id)
	{
		Cookie::set($this->_name, $new_id, $this->_lifetime);
		$this->id = $new_id;
		$this->read($new_id);
	}

	/**
	 * @param   string  $id  session id
	 * @return  string
	 */
	protected function _read($id = NULL)
	{
		if ( ! $id)
			$id = $this->id();

		if ($id)
		{
			$data = '';
			foreach ($this->pdo->query('SELECT data FROM sessions WHERE id = '.$this->pdo->quote($id)) as $row)
				$data = $row['data'];

			return $data;
		}

		return FALSE;
	}

	/**
	 * @return  null
	 */
	protected function _regenerate()
	{
		// Clean out sessions rows in the database that havent been used for a week
		$this->pdo->exec('DELETE FROM sessions WHERE updated < '.$this->pdo->quote(date('Y-m-d H:i:s', time() - (7 * 24 * 3600))));

		// Generate a new id and make sure it does not exists in the database already
		$new_id = FALSE;
		while ( ! $new_id)
		{
			$new_id = Text::random(NULL, 16);
			if ($this->pdo->query('SELECT updated FROM sessions WHERE id = '.$this->pdo->quote($new_id))->fetchColumn())
				$new_id = FALSE;
		}

		// Save this row to database
		$this->pdo->exec('INSERT INTO sessions (id) VALUES('.$this->pdo->quote($new_id).')');

		// Write session cookie
		Cookie::set($this->_name, $new_id, $this->_lifetime);

		// Set instance id
		$this->id = $new_id;

		return $this->id();
	}

	/**
	 * @return  bool
	 */
	protected function _write()
	{
		if ($this->_data === array())
			$this->_destroy();
		else
		{
			if ( ! $this->id()) $this->_regenerate();

			$this->pdo->exec('REPLACE INTO sessions (id, data) VALUES('.$this->pdo->quote($this->id()).','.$this->pdo->quote($this->__toString()).')');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return  bool
	 */
	protected function _restart()
	{
		$this->_regenerate();
		$this->_data = array();

		return TRUE;
	}

	/**
	 * @return  bool
	 */
	protected function _destroy()
	{
		if ($this->id())
		{
			$this->pdo->exec('DELETE FROM sessions WHERE id = '.$this->pdo->quote($this->id()));
			$this->_data = array();
			$this->id    = NULL;
			return Cookie::delete($this->_name);
		}

		return TRUE;
	}

}