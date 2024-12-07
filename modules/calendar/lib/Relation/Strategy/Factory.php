<?php

namespace Bitrix\Calendar\Relation\Strategy;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;

class Factory
{
	protected static ?Factory $instance = null;

	public static function getInstance(): Factory
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct()
	{}

	public function getStrategy(int $userId, Event $event): RelationStrategy
	{
		if ($event->getSpecialLabel() === Dictionary::EVENT_TYPE['shared_crm'])
		{
			return new CrmSharingStrategy($userId, $event);
		}

		return new NullStrategy($userId, $event);
	}
}