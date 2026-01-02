<?php

namespace Bitrix\Main\Data\LocalStorage\Storage;

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;

class CacheStorage implements StorageInterface
{
	private const CACHE_DIR = 'local-session';

	/** @var string */
	private $baseDir;
	/** @var CacheEngineInterface */
	private $cacheEngine;

	public function __construct(CacheEngineInterface $cacheEngine)
	{
		$this->cacheEngine = $cacheEngine;
		$this->baseDir = Application::getPersonalRoot() . '/cache/';
	}

	public function read(string $key, int $ttl)
	{
		$initDir = $this->getDirname($key);
		$filename = $this->getFilename($key);

		if ($this->cacheEngine->read($value, $this->baseDir, $initDir, $filename, $ttl))
		{
			return $value;
		}

		return null;
	}

	public function write(string $key, $value, int $ttl)
	{
		$initDir = $this->getDirname($key);
		$filename = $this->getFilename($key);

		$this->cacheEngine->write($value, $this->baseDir, $initDir, $filename, $ttl);
	}

	private function getDirname(string $key): string
	{
		return self::CACHE_DIR . '/'. substr(md5($key), 0, 3);
	}

	private function getFilename(string $key): string
	{
		return '/' . Cache::getPath($key);
	}
}
