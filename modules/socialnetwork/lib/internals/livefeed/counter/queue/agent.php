<?php

namespace Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Queue;

use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\CounterController;

class Agent
{
	private static bool $processing = false;

	public static function execute()
	{
		if (self::$processing)
		{
			return self::getAgentName();
		}

		self::$processing = true;

		$queue = Queue::getInstance();
		$rows = $queue->get(CounterController::STEP_LIMIT);

		if (empty($rows))
		{
			self::$processing = false;
			return '';
		}

		foreach ($rows as $row)
		{
			$userId = (int)$row['USER_ID'];
			(new CounterController($userId))->processQueueItem($row);
		}

		$queue->done();

		self::$processing = false;

		return self::getAgentName();
	}

	public function __construct()
	{

	}

	public function addAgent(): void
	{
		$res = \CAgent::GetList(
			['ID' => 'DESC'],
			[
				'=NAME' => self::getAgentName()
			]
		);
		if ($res->Fetch())
		{
			return;
		}

		\CAgent::AddAgent(
			self::getAgentName(),
			'socialnetwork',
			'N',
			0,
			'',
			'Y',
			''
		);
	}

	public function removeAgent(): void
	{
		\CAgent::RemoveAgent(self::getAgentName(), 'socialnetwork');
	}

	private static function getAgentName(): string
	{
		return static::class . "::execute();";
	}
}