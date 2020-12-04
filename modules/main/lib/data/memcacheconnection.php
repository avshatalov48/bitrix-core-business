<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2012 Bitrix
 */

namespace Bitrix\Main\Data;

/**
 * Class MemcacheConnection
 * @package Bitrix\Main\Data
 * @property \Memcache $resource
 * @method getResource(): \Memcache
 */
class MemcacheConnection extends NosqlConnection
{
	/** @var Configurator\MemcacheConnectionConfigurator */
	protected $configurator;

	public function __construct(array $configuration)
	{
		parent::__construct($configuration);

		$this->configurator = new Configurator\MemcacheConnectionConfigurator($this->getConfiguration());
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
			$this->resource->close();
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
