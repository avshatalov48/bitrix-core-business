<?php

namespace Bitrix\Calendar\Sync\Connection;

class Server implements ServerInterface
{
	private const SCHEME_SEPARATOR = '://';
	private const PORT_SEPARATOR = ':';
	// public const DEFAULT_PORT = 443;

	/**
	 * @var string
	 */
	protected $scheme;
	/**
	 * @var string
	 */
	protected $host;
	/**
	 * @var string
	 */
	protected $port;
	/**
	 * @var string
	 */
	protected $basePath;
	/**
	 * @var ?string
	 */
	protected $userName;
	/**
	 * @var ?string
	 */
	protected $password;

	public function __construct($data)
	{
		$this->scheme = $data['SERVER_SCHEME'];
		$this->host = $data['SERVER_HOST'];
		$this->port = $data['SERVER_PORT'];
		$this->basePath = $data['SERVER_PATH'];
		$this->userName = $data['SERVER_USERNAME'];
		$this->password = $data['SERVER_PASSWORD'];
	}

	public function getHost(): string
	{
		return $this->host;
	}

	public function getScheme(): string
	{
		return $this->scheme;
	}

	public function getPort(): string
	{
		return $this->port;
	}

	public function getBasePath(): string
	{
		return $this->basePath;
	}

	public function getUserName(): ?string
	{
		return $this->userName;
	}

	public function getPassword(): ?string
	{
		return $this->password;
	}

	public function setPassword($password): Server
	{
		$this->password = $password;

		return $this;
	}

	public function getFullPath(): string
	{
		return $this->getScheme()
			. self::SCHEME_SEPARATOR
			. $this->getHost()
			. self::PORT_SEPARATOR
			. $this->getPort()
			. $this->getBasePath()
		;
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	public static function getEncodePath(string $path): string
	{
		return urlencode($path);
	}

	/**
	 * @param string $uri
	 * @param array $map
	 * @return string
	 */
	public static function mapUri(string $uri, array $map): string
	{
		return str_replace(array_keys($map), $map, $uri);
	}
}
