<?php

namespace Bitrix\Mail\Helper\Cache;

use Bitrix\Main\Data\Cache;

/**
 * Cache reader/writer for sanitized email body
 */
final class SanitizedBodyCache
{
	/**
	 * Cache TTL
	 *
	 * @var int
	 */
	private const TTL = 10 * 60;

	/**
	 * Cache directory
	 */
	private const DIR = 'mail';

	/**
	 * Get cache ID
	 *
	 * @param int $messageId Message Id
	 *
	 * @return string
	 */
	private function getId(int $messageId): string
	{
		return "mail_ajax_body_$messageId";
	}

	/**
	 * Get body by ID
	 *
	 * @param int $messageId Message ID
	 *
	 * @return string|null
	 */
	public function get(int $messageId): ?string
	{
		$cache = Cache::createInstance();
		if ($cache->initCache(self::TTL, $this->getId($messageId), self::DIR))
		{
			return $cache->getVars();
		}

		return null;
	}

	/**
	 * Set ajax body id
	 *
	 * @param int $messageId Message ID
	 * @param string $value
	 *
	 * @return void
	 */
	public function set(int $messageId, string $value): void
	{
		$cache = Cache::createInstance();
		$cache->initCache(self::TTL, $this->getId($messageId), self::DIR);
		$cache->startDataCache();
		$cache->endDataCache($value);
	}

}
