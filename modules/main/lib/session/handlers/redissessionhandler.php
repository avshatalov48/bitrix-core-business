<?php

namespace Bitrix\Main\Session\Handlers;

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Data\RedisConnection;

class RedisSessionHandler extends AbstractSessionHandler
{
	public const SESSION_REDIS_CONNECTION = 'session.redis';

	/** @var \Redis | \RedisCluster $connection */
	protected $connection;
	protected string $prefix;
	protected bool $exclusiveLock;

	public function __construct(array $options)
	{
		$this->readOnly = $options['readOnly'] ?? false; //defined('BX_SECURITY_SESSION_READONLY');
		$this->prefix = $options['keyPrefix'] ?? 'BX'; //defined("BX_CACHE_SID") ? BX_CACHE_SID : "BX"
		$this->exclusiveLock = $options['exclusiveLock'] ?? false; //defined('BX_SECURITY_SESSION_REDIS_EXLOCK') && BX_SECURITY_SESSION_REDIS_EXLOCK

		$connectionPool = Application::getInstance()->getConnectionPool();
		$connectionPool->setConnectionParameters(self::SESSION_REDIS_CONNECTION, [
			'className' => RedisConnection::class,
			'host' => $options['host'] ?? '127.0.0.1',
			'port' => (int)($options['port'] ?? 11211),
			'servers' => $options['servers'] ?? [],
			'serializer' => $options['serializer'] ?? null,
			'failover' => $options['failover'] ?? null,
			'timeout' => $options['timeout'] ?? null,
			'readTimeout' => $options['readTimeout'] ?? null,
			'persistent' => $options['persistent'] ?? null,
		]);
	}

	public function open($savePath, $sessionName): bool
	{
		return $this->createConnection();
	}

	public function close(): bool
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

		$this->connection->del($this->getPrefix() . $sessionId);

		if ($isConnectionRestored)
		{
			$this->closeConnection();
		}

		return true;
	}

	public function gc($maxLifeTime): int
	{
		return 0;
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
		$connectionPool = Application::getInstance()->getConnectionPool();
		/** @var RedisConnection $redisConnection */
		$redisConnection = $connectionPool->getConnection(self::SESSION_REDIS_CONNECTION);
		$this->connection = $redisConnection->getResource();

		return $redisConnection->isConnected();
	}

	protected function closeConnection(): void
	{
		if ($this->isConnected())
		{
			$this->connection->close();
		}

		$this->connection = null;
	}

	public function updateTimestamp($sessionId, $sessionData): bool
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
				$errorText = '';
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
		$this->connection->del($this->getPrefix() . "{$sessionId}.lock");

		return true;
	}
}