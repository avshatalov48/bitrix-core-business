<?php
namespace Bitrix\Im;

class NotifyFlash
{
	static $instance = null;

	private $notify = [];
	private $commitList = [];

	const TYPE_NOTIFY = 'notify';
	const TYPE_MESSAGE = 'message';

	const ENGINE_SESSION = 'session';
	const ENGINE_CACHE = 'cache';

	private $cacheTtl = null;
	private $cacheDir = null;

	public static function getInstance(): self
	{
		if (!self::$instance)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function getEngineType(): string
	{
		return (string)\Bitrix\Main\Config\Option::get('im', 'notify_flash_engine_type', self::ENGINE_SESSION);
	}

	public function setEngineType(string $type): bool
	{
		if (!in_array($type, [self::ENGINE_SESSION, self::ENGINE_CACHE], true))
		{
			return false;
		}

		\Bitrix\Main\Config\Option::set('im', 'notify_flash_engine_type', $type);

		return true;
	}

	private function __construct()
	{
		if ($this->getEngineType() === self::ENGINE_SESSION)
		{
			$this->initSession();
		}
		else if ($this->getEngineType() === self::ENGINE_CACHE)
		{
			$this->initCache();
		}
	}

	public function set(string $type, $id): bool
	{
		if (!isset($this->notify[$type]))
		{
			return false;
		}

		if (isset($this->notify[$type][$id]))
		{
			return true;
		}

		$this->notify[$type][$id] = true;
		$this->commitList[$id] = $type;

		return true;
	}

	public function exists(string $type, $id): bool
	{
		return isset($this->notify[$type][$id]);
	}

	public function commit(): bool
	{
		if ($this->getEngineType() === self::ENGINE_SESSION)
		{
			$this->commitSession();
		}
		else if ($this->getEngineType() === self::ENGINE_CACHE)
		{
			$this->commitCache();
		}

		return true;
	}

	/* session block */

	private function initSession(): bool
	{
		$this->notify = [
			self::TYPE_NOTIFY => $_SESSION['IM_FLASHED_NOTIFY']?: [],
			self::TYPE_MESSAGE => $_SESSION['IM_FLASHED_MESSAGE']?: [],
		];

		return true;
	}

	private function commitSession(): bool
	{
		if (empty($this->commitList))
		{
			return true;
		}

		foreach ($this->commitList as $id => $type)
		{
			if ($type === self::TYPE_NOTIFY)
			{
				$_SESSION['IM_FLASHED_NOTIFY'][$id] = true;
			}
			else if ($type === self::TYPE_MESSAGE)
			{
				$_SESSION['IM_FLASHED_MESSAGE'][$id] = true;
			}

			$this->notify[$type][$id] = true;
			unset($this->commitList[$id]);
		}

		return true;
	}

	/* cache block */

	private function getCacheId(): string
	{
		global $USER;

		return 'flash_'.$USER->GetID();
	}

	private function initCache(): bool
	{
		$this->notify = [
			self::TYPE_NOTIFY => [],
			self::TYPE_MESSAGE => [],
		];

		$this->cacheDir = '/bx/im/flash/';
		$this->cacheTtl = 8*60*60;

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if($cache->initCache($this->cacheTtl, $this->getCacheId(), $this->cacheDir))
		{
			$this->notify = $cache->getVars();
			return true;
		}

		return true;
	}

	private function commitCache(): bool
	{
		if (empty($this->commitList))
		{
			return true;
		}

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->initCache($this->cacheTtl, $this->getCacheId(), $this->cacheDir);

		foreach ($this->commitList as $id => $type)
		{
			$this->notify[$type][$id] = true;
			unset($this->commitList[$id]);
		}

		$cache->startDataCache();
		$cache->endDataCache($this->notify);

		return true;
	}
}