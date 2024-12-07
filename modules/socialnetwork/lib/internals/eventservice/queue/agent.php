<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Queue;

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

		$rowsProcessed = Queue::getInstance()->process();

		self::$processing = false;

		return ($rowsProcessed > 0) ? self::getAgentName() : '';
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