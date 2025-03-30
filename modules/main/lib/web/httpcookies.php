<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Main\Web;

use Bitrix\Main\ArgumentTypeException;

/**
 * @property Http\Cookie[] $values
 */
class HttpCookies extends \Bitrix\Main\Type\Dictionary
{
	/**
	 * @param string[] | Http\Cookie[] | null $values
	 */
	public function __construct(array $values = null)
	{
		if ($values !== null)
		{
			foreach ($values as $key => $value)
			{
				if (!($value instanceof Http\Cookie))
				{
					$value = new Http\Cookie($key, $value);
				}
				$this[$key] = $value;
			}
		}
	}

	/**
	 * Implodes cookies to 'name=value' pairs with a '; ' separator (useful for 'Cookie' header).
	 * @return string
	 */
	public function implode(): string
	{
		$str = '';
		foreach ($this->values as $cookie)
		{
			$str .= ($str == '' ? '' : '; ') . rawurlencode($cookie->getName()) . '=' . rawurlencode($cookie->getValue());
		}

		return $str;
	}

	public function addFromString(string $str): void
	{
		if (($pos = strpos($str, ';')) !== false && $pos > 0)
		{
			$cookie = trim(substr($str, 0, $pos));
		}
		else
		{
			$cookie = trim($str);
		}
		$cookies = explode('=', $cookie, 2);

		$name = rawurldecode($cookies[0]);
		$value = rawurldecode($cookies[1]);

		// TODO: a cookie has more attributes
		$this[$name] = new Http\Cookie($name, $value);
	}

	public function toArray()
	{
		$cookies = [];
		foreach ($this->values as $cookie)
		{
			$cookies[$cookie->getName()] = $cookie->getValue();
		}

		return $cookies;
	}

	#[\ReturnTypeWillChange]
	public function offsetSet($offset, $value)
	{
		if (!($value instanceof Http\Cookie))
		{
			throw new ArgumentTypeException('value', Http\Cookie::class);
		}
		parent::offsetSet($offset, $value);
	}
}
