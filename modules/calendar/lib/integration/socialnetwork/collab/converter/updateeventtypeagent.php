<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Integration\SocialNetwork\Collab\Converter;

use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Internals\EventTable;

class UpdateEventTypeAgent
{
	private const AGENT_STEP = 30;

	public static function runAgent(int $groupId, string $newEventType, int $lastId): string
	{
		$runner = new self($groupId, $newEventType, $lastId);
		[$needToContinue, $lastProcessedId] = $runner->run();

		if (!$needToContinue)
		{
			\CCalendar::ClearCache(['event_list']);

			return '';
		}

		return self::makeAgentName($groupId, $newEventType, (int)$lastProcessedId);
	}

	public function __construct(
		public readonly int $groupId,
		public readonly string $newEventType,
		public readonly int $lastId,
	)
	{
	}

	public function run(): array
	{
		$toMin = time() + 12 * 3600;

		$events = EventTable::query()
			->setSelect(['ID', 'PARENT_ID'])
			->where('ID', '>', $this->lastId)
			->where('CAL_TYPE', Dictionary::CALENDAR_TYPE['group'])
			->where('OWNER_ID', $this->groupId)
			->where('DELETED', 'N')
			->where('DATE_TO_TS_UTC', '>=', $toMin)
			->setOrder(['ID' => 'ASC'])
			->setLimit(self::AGENT_STEP)
			->fetchCollection()
		;

		$toUpdate = [];
		$lastProcessedId = 0;
		foreach ($events as $event)
		{
			$toUpdate[] = $event->getParentId();
			$lastProcessedId = $event->getId();
		}

		if (!empty($toUpdate))
		{
			EventTable::updateByFilter(['PARENT_ID' => $toUpdate], ['EVENT_TYPE' => $this->newEventType]);
		}

		$needToContinue = $events->count() === self::AGENT_STEP;

		return [$needToContinue, $lastProcessedId];
	}

	public static function makeAgentName(int $groupId, string $newEventType, int $lastId = 0): string
	{
		$params = $groupId . ", '" . $newEventType . "', " . $lastId;

		return self::class . '::runAgent(' . $params . ');';
	}
}