<?php

namespace Bitrix\Calendar\Sync\Util;

use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Core;

/**
 * @property Core\Section\Section|null $section
 * @property mixed|null $server
 * @property mixed|null $sync
 * @property Sync\Connection\SectionConnection|null $sectionConnections
 * @property Core\Section\SectionMap|null sections
 * @property Sync\Connection\ConnectionMap|null connections
 * @property Sync\Connection\EventConnectionMap|null eventConnections
 */
class Context
{
	/**
	 * @var array
	 */
	private array $data;

	public function __construct(array $data = [])
	{
		$this->data = $data;
	}

	public function __get($name)
	{
		return $this->data[$name] ?? null;
	}

	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}

	public function __isset($name)
	{
		return isset($this->data[$name]);
	}

	/**
	 * you should add property
	 * e.g. you should add service data
	 *
	 * @param string $property
	 * @param mixed $value
	 *
	 * @return $this
	 */
	public function add(string $type, string $property, $value): Context
	{
		$this->data[$type][$property] = $value;

		return $this;
	}

	/**
	 * merge Context
	 *
	 * @param Context $context
	 * @return $this
	 */
	public function merge(Context $context): Context
	{
		$this->data = array_merge($this->data, $context->data);

		return $this;
	}

	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getByKey(string $key)
	{
		if (isset($this->data[$key]))
		{
			return null;
		}

		return $this->data[$key];
	}
}
