<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */
namespace Bitrix\Main\Web;

class IpAddress
{
	protected $ip;

	/**
	 * @param string $ip
	 */
	public function __construct($ip)
	{
		$this->ip = $ip;
	}

	/**
	 * Creates the object by a host name.
	 *
	 * @param string $name
	 * @return static
	 */
	public static function createByName($name)
	{
		$ip = gethostbyname($name);
		return new static($ip);
	}

	/**
	 * Creates the object by an Uri.
	 *
	 * @param Uri $uri
	 * @return static
	 */
	public static function createByUri(Uri $uri)
	{
		return static::createByName($uri->getHost());
	}

	/**
	 * Returns address's value.
	 *
	 * @return string
	 */
	public function get()
	{
		return $this->ip;
	}

	/**
	 * Retuns true if the address is incorrect or private.
	 *
	 * @return bool
	 */
	public function isPrivate()
	{
		return (filter_var($this->ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false);
	}

	/**
	 * Check IPv4 address is within an IP range
	 *
	 * @param string $cidr a valid IPv4 subnet[/mask]
	 * @return bool
	 */
	public function matchRange(string $cidr): bool
	{
		if (strpos($cidr,'/') !== false)
		{
			[$subnet, $mask] = explode('/', $cidr);
		}
		else
		{
			$subnet = $cidr;
			$mask = 32;
		}

		return (ip2long($this->ip) & ~((1 << (32 - $mask)) - 1)) === ip2long($subnet);
	}
}
