<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2012 Bitrix
 */

namespace Bitrix\Main\Data;

/**
 * Class description
 * @package    bitrix
 * @subpackage main
 * @property \Memcached $resource
 */
class MemcachedConnection extends NosqlConnection
{
	protected Configurator\MemcachedConnectionConfigurator $configurator;

	public function __construct(array $configuration)
	{
		parent::__construct($configuration);
		$this->configurator = new Configurator\MemcachedConnectionConfigurator($this->getConfiguration());
	}

	protected function connectInternal()
	{
		$this->resource = $this->configurator->createConnection();
		$this->isConnected = (bool)$this->resource;
	}

	protected function disconnectInternal()
	{
		if ($this->isConnected())
		{
			$this->resource->quit();
			$this->resource = null;
			$this->isConnected = false;
		}
	}

	public function get($key)
	{
		if (!$this->isConnected())
		{
			$this->connect();
		}

		return $this->resource->get($key);
	}

	public function set($key, $value)
	{
		if (!$this->isConnected())
		{
			$this->connect();
		}

		return $this->resource->set($key, $value);
	}
}