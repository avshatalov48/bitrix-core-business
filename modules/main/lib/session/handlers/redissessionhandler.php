<?php

namespace Bitrix\Main\Session\Handlers;

use Bitrix\Main\Context;

class RedisSessionHandler extends AbstractSessionHandler
{
	/** @var \Redis $connection */
	protected $connection;
	/** @var string */
	protected $prefix;
	/** @var int */
	protected $port;
	/** @var string */
	protected $host;
	/** @var bool */
	protected $exclusiveLock;

	public function __construct(array $options)
	{
		$this->readOnly = $options['readOnly'] ?? false; //defined('BX_SECURITY_SESSION_READONLY');
		$this->prefix = $options['keyPrefix'] ?? 'BX'; //defined("BX_CACHE_SID") ? BX_CACHE_SID : "BX"
		$this->port = (int)($options['port'] ?? 11211); //defined("BX_SECURITY_SESSION_REDIS_PORT") ? intval(BX_SECURITY_SESSION_REDIS_PORT) : 11211
		$this->host = $options['host']; //BX_SECURITY_SESSION_REDIS_HOST
		$this->exclusiveLock = $options['exclusiveLock'] ?? false; //defined('BX_SECURITY_SESSION_REDIS_EXLOCK') && BX_SECURITY_SESSION_REDIS_EXLOCK
	}

	public function open($savePath, $sessionName)
	{
		return $this->createConnection();
	}

	public function close()
	{
		parent::close();
		$this->closeConnection();

		return true;
	}

	public function processRead($sessionId): string
	{
		$result = $this->connection->get($this->getPrefix() . $sessionId);

		return $result?: "";
	}

	public function processWrite($sessionId, $sessionData): bool
	{
		$maxLifetime = (int)ini_get("session.gc_maxlifetime");

		$this->connection->setex($this->getPrefix() . $sessionId, $maxLifetime, $sessionData);

		return true;
	}

	public function processDestroy($sessionId): bool
	{
		$isConnectionRestored = false;
		if (!$this->isConnected())
		{
			$isConnectionRestored = $this->createConnection();
		}

		if (!$this->isConnected())
		{
			return false;
		}

		$this->connection->delete($this->getPrefix() . $sessionId);

		if ($isConnectionRestored)
		{
			$this->closeConnection();
		}

		return true;
	}

	public function gc($maxLifeTime)
	{
		return true;
	}

	protected function isConnected(): bool
	{
		return $this->connection !== null;
	}

	protected function getPrefix(): string
	{
		return $this->prefix;
	}

	protected function createConnection(): bool
	{
		$exception = null;
		if (!extension_loaded('redis'))
		{
			$result = false;
			$exception = new \ErrorException("redis extension is not loaded.", 0, E_USER_ERROR, __FILE__, __LINE__);
		}
		else
		{
			$this->connection = new \Redis();
			$result = $this->connection->pconnect($this->host, $this->port);
			if ($result)
			{
				$this->connection->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_IGBINARY);
			}
			else
			{
				$error = error_get_last();
				if ($error && $error["type"] == E_WARNING)
				{
					$exception = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
				}
			}
		}

		if ($exception)
		{
			$application = \Bitrix\Main\Application::getInstance();
			$exceptionHandler = $application->getExceptionHandler();
			$exceptionHandler->writeToLog($exception);
		}

		return $result;
	}

	protected function closeConnection(): void
	{
		$this->connection->close();
		$this->connection = null;
	}

	public function updateTimestamp($sessionId, $sessionData)
	{
		return $this->write($sessionId, $sessionData);
	}

	protected function lock($sessionId): bool
	{
		$sid = $this->getPrefix();
		$lockTimeout = 55;//TODO: add setting
		$lockWait = 59000000;//micro seconds = 60 seconds TODO: add setting
		$waitStep = 100;

		$lock = 1;
		if ($this->exclusiveLock)
		{
			$lock = Context::getCurrent()->getRequest()->getRequestedPage();
		}

		while (!$this->connection->setnx($sid . $sessionId . ".lock", $lock))
		{
			usleep($waitStep);
			$lockWait -= $waitStep;
			if ($lockWait < 0)
			{
				$errorText = 'Unable to get session lock within 60 seconds.';
				if ($lock !== 1)
				{
					$lockedUri = $this->connection->get($sid . $sessionId . ".lock");
					if ($lockedUri && $lockedUri != 1)
					{
						$errorText .= sprintf(' Locked by "%s".', $lockedUri);
					}
				}

				$this->triggerLockFatalError($errorText);
			}

			if ($waitStep < 1000000)
			{
				$waitStep *= 2;
			}
		}
		$this->connection->expire($sid . $sessionId . ".lock", $lockTimeout);

		return true;
	}

	protected function unlock($sessionId): bool
	{
		$this->connection->delete($this->getPrefix() . "{$sessionId}.lock");

		return true;
	}
}