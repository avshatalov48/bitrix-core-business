<?php

namespace Bitrix\Main\Data\Configurator;

use Bitrix\Main\Application;
use Bitrix\Main\NotSupportedException;

class RedisConnectionConfigurator
{
	/** @var array */
	protected $config;
	/** @var array */
	protected $servers = [];

	public function __construct($config)
	{
		if (!extension_loaded('redis'))
		{
			throw new NotSupportedException('redis extension is not loaded.');
		}

		$this->config = $config;

		$this->addServers($this->getConfig());
	}

	protected function addServers($config)
	{
		$servers = $config['servers'] ?? [];

		if (empty($servers) && isset($config['host'], $config['port']))
		{
			array_unshift($servers, [
				'host' => $config['host'],
				'port' => $config['port'],
			]);
		}

		foreach ($servers as $server)
		{
			$this->servers[] = [
				'host' => $server['host'] ?? 'localhost',
				'port' => $server['port'] ?? '6379',
			];
		}

		return $this;
	}

	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * @param \Redis|\RedisCluster $connection
	 */
	protected function configureConnection($connection): void
	{
		$config = $this->getConfig();

		if ($connection instanceof \Redis)
		{
			$connection->setOption(\Redis::OPT_SERIALIZER, $config['serializer'] ?? \Redis::SERIALIZER_IGBINARY);
		}
		elseif ($connection instanceof \RedisCluster)
		{
			$connection->setOption(
				\RedisCluster::OPT_SERIALIZER,
				$config['serializer'] ?? \RedisCluster::SERIALIZER_IGBINARY
			);

			if (count($this->servers) > 1)
			{
				$connection->setOption(
					\RedisCluster::OPT_SLAVE_FAILOVER,
					$config['failover'] ?? \RedisCluster::FAILOVER_NONE
				);
			}
		}
	}

	public function createConnection()
	{
		$config = $this->getConfig();
		if (!$this->servers)
		{
			throw new NotSupportedException('Empty server list to redis connection.');
		}

		if (count($this->servers) === 1)
		{
			['host' => $host, 'port' => $port] = $this->servers[0];
			$connection = new \Redis();

			if ($config['persistent'])
			{
				$result = $connection->pconnect($host, $port);
			}
			else
			{
				$result = $connection->connect($host, $port);
			}
		}
		else
		{
			$connections = [];
			foreach ($this->servers as $server)
			{
				$connections[] = $server['host'] . ':' . $server['port'];
			}

			$connection = new \RedisCluster(
				null,
				$connections,
				$config['timeout'] ?? null,
				$config['readTimeout'] ?? null,
				$config['persistent'] ?? true
			);
			$result = true;
		}

		if ($result)
		{
			$this->configureConnection($connection);
		}
		else
		{
			$error = error_get_last();
			if (isset($error["type"]) && $error["type"] === E_WARNING)
			{
				$exception = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
				$application = Application::getInstance();
				$exceptionHandler = $application->getExceptionHandler();
				$exceptionHandler->writeToLog($exception);
			}
		}

		return $result? $connection : null;
	}
}