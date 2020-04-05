<?php

namespace Bitrix\Main\Mail\Smtp;

class Config
{

	protected $from, $host, $port, $login, $password;

	public function __construct(array $params = null)
	{
		if (!empty($params) && is_array($params))
		{
			foreach ($params as $name => $value)
			{
				$setter = sprintf('set%s', $name);
				if (is_callable(array($this, $setter)))
					$this->$setter($value);
			}
		}
	}

	public function setFrom($from)
	{
		$this->from = $from;
		return $this;
	}

	public function setHost($host)
	{
		$this->host = $host;
		return $this;
	}

	public function setPort($port)
	{
		$this->port = (int) $port;
		return $this;
	}

	public function setLogin($login)
	{
		$this->login = $login;
		return $this;
	}

	public function setPassword($password)
	{
		$this->password = $password;
		return $this;
	}

	public function getFrom()
	{
		return $this->from;
	}

	public function getHost()
	{
		return $this->host;
	}

	public function getPort()
	{
		return $this->port;
	}

	public function getLogin()
	{
		return $this->login;
	}

	public function getPassword()
	{
		return $this->password;
	}

}
