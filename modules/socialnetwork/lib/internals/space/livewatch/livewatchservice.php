<?php

namespace Bitrix\Socialnetwork\Internals\Space\LiveWatch;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlExpression;

class LiveWatchService
{
	private const TTL = 900; // 15 minutes
	private const LIMIT = 5000;

	private $whoisWatchingNow;

	private static $instance;

	public static function getInstance(): self
	{
		if (!self::$instance)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct()
	{

	}

	public function setUserAsWatchingNow(int $userId): void
	{
		$helper = Application::getConnection()->getSqlHelper();
		$now = new SqlExpression($helper->getCurrentDateTimeFunction());

		$insertFields = [
			"USER_ID" => $userId,
			"DATETIME" => $now,
		];

		$updateFields = [
			"DATETIME" => $now,
		];

		LiveWatchTable::merge($insertFields, $updateFields);

		if ($this->whoisWatchingNow)
		{
			$this->whoisWatchingNow[$userId] = true;
		}
	}

	public function isUserWatchingSpaces(int $userId): bool
	{
		if (!$this->whoisWatchingNow)
		{
			$this->whoisWatchingNow = [];
			$helper = Application::getConnection()->getSqlHelper();
			$ttlSecondsBack = new SqlExpression($helper->addSecondsToDateTime(-self::TTL));

			$res = LiveWatchTable::getList([
				'select' => ['USER_ID', 'DATETIME'],
				'filter' => [
					'>DATETIME' => $ttlSecondsBack,
				],
				'limit' => self::LIMIT,
			]);

			if (!$res)
			{
				return false;
			}

			foreach ($res as $watching)
			{
				$this->whoisWatchingNow[$watching['USER_ID']] = true;
			}
		}

		return isset($this->whoisWatchingNow[$userId]);
	}

	public function removeStaleRecords(): void
	{
		$helper = Application::getConnection()->getSqlHelper();
		$ttlSecondsBack = new SqlExpression($helper->addSecondsToDateTime(-self::TTL));

		LiveWatchTable::deleteByFilter([
			'<DATETIME' => $ttlSecondsBack,
		]);
	}
}