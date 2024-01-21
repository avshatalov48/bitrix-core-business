<?php

namespace Bitrix\Bizproc\Integration\Push;

use Bitrix\Main;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Pull;

final class PushWorker
{
	private bool $canUse;
	private static $setJob = false;
	private static $queue = [];

	public function __construct()
	{
		$this->canUse = Loader::includeModule('pull');
	}

	public function subscribe(int $userId, string $command): void
	{
		if ($this->canUse)
		{
			\CPullWatch::Add($userId, $command);
		}
	}

	public function send(string $command, array $params, array $userIds = []): void
	{
		if (empty($userIds))
		{
			$userIds = [CurrentUser::get()?->getId() ?? 0];
		}

		if ($this->canUse)
		{
			Pull\Event::add(
				$userIds,
				[
					'module_id' => 'bizproc',
					'command' => $command,
					'params' => $params,
				]
			);
		}
	}

	public function sendLast(string $tag, string $command, array $params, array $userIds = [])
	{
		if ($this->canUse)
		{
			self::$queue[$tag] = [$command, $params, $userIds];
			$this->setBackgroundJob();
		}
	}

	private function setBackgroundJob()
	{
		if (!self::$setJob)
		{
			Main\Application::getInstance()->addBackgroundJob(
				[__CLASS__, 'doBackgroundJob'],
				[],
				Main\Application::JOB_PRIORITY_LOW - 10
			);
			self::$setJob = true;
		}
	}

	public static function doBackgroundJob()
	{
		$push = new self();

		foreach (self::$queue as [$command, $params, $userIds])
		{
			$push->send($command, $params, $userIds);
		}
	}
}
