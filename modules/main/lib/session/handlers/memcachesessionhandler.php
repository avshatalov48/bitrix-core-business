<?php

namespace Bitrix\Main\Session\Handlers;

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Data\MemcacheConnection;

class MemcacheSessionHandler extends AbstractSessionHandler
{
	public const SESSION_MEMCACHE_CONNECTION = 'session.memcache';

	/** @var \Memcache $connection */
	protected $connection;
	/** @var string */
	protected $prefix;
	/** @var bool */
	protected $exclusiveLock;

	public function __construct(array $options)
	{
		$this->readOnly = $options['readOnly'] ?? false; //defined('BX_SECURITY_SESSION_READONLY');
		$this->prefix = $options['keyPrefix'] ?? 'BX'; //defined("BX_CACHE_SID") ? BX_CACHE_SID : "BX"
		$this->exclusiveLock = $options['exclusiveLock'] ?? false; //defined('BX_SECURITY_SESSION_MEMCACHE_EXLOCK') && BX_SECURITY_SESSION_MEMCACHE_EXLOCK

		$connectionPool = Application::getInstance()->getConnectionPool();
		$connectionPool->setConnectionParameters(self::SESSION_MEMCACHE_CONNECTION, [
			'className' => MemcacheConnection::class,
			'host' => $options['host'] ?? '127.0.0.1',
			'port' => (int)($options['port'] ?? 11211),
			'connectionTimeout' => $options['connectionTimeout'] ?? 1,
			'servers' => $options['servers'] ?? [],
		]);

	}

	public function open($savePath, $sessionName): bool
	{
		$this->createConnection();

		return $this->isConnected();
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

		$this->connection->set($this->getPrefix() . $sessionId, $sessionData, 0, $maxLifetime);

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

		$this->connection->replace($this->getPrefix() . $sessionId, "", 0, 1);

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
		/** @var MemcacheConnection $redisConnection */
		$memcacheConnection = $connectionPool->getConnection(self::SESSION_MEMCACHE_CONNECTION);
		if (!$memcacheConnection)
		{
			return false;
		}

		$this->connection = $memcacheConnection->getResource();

		return (bool)$this->connection;
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

		while (!$this->connection->add($sid . $sessionId . ".lock", $lock, 0, $lockTimeout))
		{
			if ($this->connection->increment($sid . $sessionId . ".lock", 1) === 1)
			{
				$this->connection->replace($sid . $sessionId . ".lock", $lock, 0, $lockTimeout);
				break;
			}

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

		return true;
	}

	protected function unlock($sessionId): bool
	{

		return $this->connection->replace($this->getPrefix() . "{$sessionId}.lock", 0, 0, 1);
	}
}