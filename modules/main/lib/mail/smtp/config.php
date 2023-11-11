<?php

namespace Bitrix\Main\Mail\Smtp;

use \Bitrix\Main;
use \Bitrix\Mail;

class Config
{

	/**
	 * Sender name
	 *
	 * @var string|null
	 */
	protected $from;

	/**
	 * Server host
	 *
	 * @var string|null
	 */
	protected $host;

	/**
	 * Server port
	 *
	 * @var int|null
	 */
	protected $port;

	/**
	 * Server protocol
	 *
	 * @var string|null
	 */
	protected $protocol;

	/**
	 * Auth login
	 *
	 * @var string|null
	 */
	protected $login;

	/**
	 * Auth password, can be oauth meta build value
	 *
	 * @var string|null
	 */
	protected $password;

	/**
	 * Is password value oauth meta build
	 *
	 * @var bool
	 */
	protected bool $isOauth = false;

	public function __construct(array $params = null)
	{
		if (!empty($params))
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

	public function setProtocol($protocol)
	{
		$this->protocol = $protocol;
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

	/**
	 * Set is OAuth flag
	 *
	 * @param mixed $isOauth Value
	 *
	 * @return $this
	 */
	public function setIsOauth($isOauth): self
	{
		$this->isOauth = (bool)$isOauth;
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

	public function getProtocol()
	{
		return $this->protocol;
	}

	public function getLogin()
	{
		return $this->login;
	}

	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * Is password value OAuth meta build
	 *
	 * @return bool
	 */
	public function getIsOauth(): bool
	{
		return $this->isOauth;
	}

	public static function canCheck()
	{
		return Main\Loader::includeModule('mail') && class_exists('Bitrix\Mail\Smtp');
	}

	public function check(&$error = null, Main\ErrorCollection &$errors = null)
	{
		$error = null;
		$errors = null;

		if (!$this->canCheck())
		{
			return null;
		}

		if (Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			// Private addresses can't be used in the cloud
			$ip = Main\Web\IpAddress::createByName($this->host);
			if ($ip->isPrivate())
			{
				$error = 'SMTP server address is invalid';
				$errors = new Main\ErrorCollection([new Main\Error($error)]);
				return false;
			}
		}

		$client = new Mail\Smtp(
			$this->host,
			$this->port,
			('smtps' === $this->protocol || ('smtp' !== $this->protocol && 465 === $this->port)),
			true,
			$this->login,
			$this->password
		);
		if (method_exists($client, 'setIsOauth'))
		{
			$client->setIsOauth($this->getIsOauth());
		}

		if (!$client->authenticate($error))
		{
			$errors = $client->getErrors();
			return false;
		}

		return true;
	}

}
