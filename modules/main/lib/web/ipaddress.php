<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Web;

use Psr\Http\Message\UriInterface;

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
	 * Creates the object by a Uri.
	 *
	 * @param UriInterface $uri
	 * @return static
	 */
	public static function createByUri(UriInterface $uri)
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
	 * Returns address's value.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->get();
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

	/**
	 * Formats IP as an unsigned int and returns it as a sting.
	 * @return string
	 */
	public function toUnsigned()
	{
		return sprintf('%u', ip2long($this->ip));
	}

	/**
	 * Formats IP as a range (192.168.0.0/24).
	 * @return string
	 */
	public function toRange(int $prefixLen)
	{
		return long2ip(ip2long($this->ip) & ~((1 << (32 - $prefixLen)) - 1)) . '/' . $prefixLen;
	}
}
