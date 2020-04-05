<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage seoproxy
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Seo\WebHook\Payload;

use Bitrix\Main\ArgumentException;

/**
 * Class Item
 *
 * @package Bitrix\Seo\WebHook\Payload
 */
abstract class Item
{
	/** @var  array $data Data. */
	protected $data = [
		'source' => null
	];

	/**
	 * Item constructor.
	 *
	 * @param array $data Data.
	 */
	public function __construct(array $data)
	{
		$this->setData($data);
	}

	/**
	 * Get data.
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Set data.
	 *
	 * @param array $data Data.
	 * @return $this
	 */
	public function setData(array $data)
	{
		foreach ($data as $key => $value)
		{
			$this->set($key, $value);
		}

		return $this;
	}

	/**
	 * Get source.
	 *
	 * @return string
	 * @throws ArgumentException
	 */
	public function get($key)
	{
		if (!array_key_exists($key, $this->data))
		{
			throw new ArgumentException("Unknown key `$key`.");
		}

		return $this->data[$key];
	}

	/**
	 * Set source.
	 *
	 * @param string $key Key.
	 * @param string|array $value Value
	 * @return $this
	 * @throws ArgumentException
	 */
	public function set($key, $value)
	{
		if (!array_key_exists($key, $this->data))
		{
			throw new ArgumentException("Unknown key `$key`.");
		}

		$this->data[$key] = $value;
		return $this;
	}

	/**
	 * Magic method __call.
	 *
	 * @param string $name Name.
	 * @param array $arguments Arguments.
	 * @return mixed
	 * @throws ArgumentException
	 */
	public function __call($name, $arguments)
	{
		$method = substr($name, 0, 3);
		if (in_array($method, ['set', 'get']))
		{
			$key = lcfirst(substr($name, 3));
			return call_user_func_array(array($this, $method), array_merge([$key], $arguments));
		}

		throw new ArgumentException("Method `$name` not found.");
	}
}

