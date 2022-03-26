<?php

namespace Bitrix\Main\Data\Configurator;

use Bitrix\Main\Application;
use Bitrix\Main\NotSupportedException;

class MemcacheConnectionConfigurator
{
	/** @var array */
	protected $config;
	/** @var array */
	protected $servers = [];

	public function __construct($config)
	{
		if (!extension_loaded('memcache'))
		{
			throw new NotSupportedException('memcache extension is not loaded.');
		}

		$this->config = $config;

		$this->addServers($this->getConfig());
	}

	protected function addServers($config)
	{
		$servers = $config['servers'] ?? [];

		if (isset($config['host'], $config['port']))
		{
			array_unshift($servers, [
				'host' => $config['host'],
				'port' => $config['port'],
			]);
		}

		foreach ($servers as $server)
		{
			if (!isset($server['weight']) || $server['weight'] <= 0)
			{
				$server['weight'] = 1;
			}

			$this->servers[] = [
				'host' => $server['host'] ?? 'localhost',
				'port' => $server['port'] ?? '11211',
				'weight' => $server['weight'],
			];
		}

		return $this;
	}

	public function getConfig()
	{
		return $this->config;
	}

	public function createConnection()
	{
		if (!$this->servers)
		{
			throw new NotSupportedException('Empty server list to memcache connection.');
		}

		$connectionTimeout = $this->getConfig()['connectionTimeout'] ?? 1;
		$connection = new \Memcache();
		if (count($this->servers) === 1)
		{
			['host' => $host, 'port' => $port] = $this->servers[0];
			$result = $connection->pconnect($host, $port, $connectionTimeout);
		}
		else
		{
			foreach ($this->servers as $server)
			{
				$result = $connection->addServer(
					$server['host'],
					$server['port'],
					true,
					$server['weight'],
					$connectionTimeout
				);
			}
		}

		if (!$result)
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