<?php

namespace Bitrix\Main\Data;

/**
 * Class RedisConnection
 * @package Bitrix\Main\Data
 * @property \Redis|\RedisCluster $resource
 * @method getResource(): \Redis|\RedisCluster
 */
class RedisConnection extends NosqlConnection
{
	/** @var Configurator\RedisConnectionConfigurator */
	protected $configurator;

	public function __construct(array $configuration)
	{
		parent::__construct($configuration);

		$this->configurator = new Configurator\RedisConnectionConfigurator($this->getConfiguration());
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