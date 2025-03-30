<?php

namespace Bitrix\Main\Data\Configurator;

use Bitrix\Main\Application;
use Bitrix\Main\NotSupportedException;

class RedisConnectionConfigurator
{
	protected array $config;
	protected array $servers = [];

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

	public function getConfig(): array
	{
		return $this->config;
	}

	protected function configureConnection(\RedisCluster|\Redis $connection): void
	{
		$config = $this->getConfig();

		if ($connection instanceof \Redis)
		{
			if (isset($config['compression']) || defined('\Redis::COMPRESSION_LZ4'))
			{
				$connection->setOption(\Redis::OPT_COMPRESSION, $config['compression'] ?? \Redis::COMPRESSION_LZ4);
				$connection->setOption(\Redis::OPT_COMPRESSION_LEVEL, $config['compression_level'] ?? \Redis::COMPRESSION_ZSTD_MAX);
			}

			if (isset($config['serializer']) || defined('\Redis::SERIALIZER_IGBINARY'))
			{
				$connection->setOption(\Redis::OPT_SERIALIZER, $config['serializer'] ?? \Redis::SERIALIZER_IGBINARY);
			}
		}
		elseif ($connection instanceof \RedisCluster)
		{
			$connection->setOption(\RedisCluster::OPT_SERIALIZER, $config['serializer'] ?? \RedisCluster::SERIALIZER_IGBINARY);

			if (count($this->servers) > 1)
			{
				$connection->setOption(\RedisCluster::OPT_SLAVE_FAILOVER, $config['failover'] ?? \RedisCluster::FAILOVER_NONE);
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

			$params = [
				$host,
				$port,
				$config['timeout'] ?? 0,
				null,
				0,
				$config['readTimeout'] ?? 0
			];

			if ($config['persistent'])
			{
				$result = $connection->pconnect(...$params);
			}
			else
			{
				$result = $connection->connect(...$params);
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