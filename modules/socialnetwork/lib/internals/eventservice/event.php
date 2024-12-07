<?php

namespace Bitrix\Socialnetwork\Internals\EventService;

use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Internals\EventService\Recepients\Collector;
use Bitrix\Socialnetwork\Internals\EventService\Recepients\Recepient;
use Bitrix\Socialnetwork\Internals\EventService\Recepients\RecepientCollection;
use Bitrix\Socialnetwork\Internals\EventService\Recepients\SonetRightsRecepient;
use Bitrix\Socialnetwork\Internals\EventService\Recepients\WorkgroupRequestRecipient;

/**
 * Class Event
 *
 * @package Bitrix\Socialnetwork\Internals\EventService\Event
 */

class Event
{
	protected int|null $eventId = null;
	protected array $data = [];
	protected DateTime $dateTime;

	public function __construct(
		protected string $hitId,
		protected string $type = ''
	)
	{
		$this->dateTime = new DateTime();
	}

	public function setId(int $eventId): self
	{
		$this->eventId = $eventId;

		return $this;
	}

	public function setData(array $data = []): self
	{
		$this->data = $this->prepareData($data);

		return $this;
	}

	public function getId(): int|null
	{
		return $this->eventId;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getDateTime(): DateTime
	{
		return $this->dateTime;
	}

	public function getData(): array
	{
		return $this->data;
	}

	public function getUserId(): int
	{
		return (int)($this->data['USER_ID'] ?? 0);
	}

	public function getGroupId(): int
	{
		return (int)($this->data['GROUP_ID'] ?? 0);
	}

	public function getEntityTypeId(): string|null
	{
		return $this->data['ENTITY_TYPE_ID'] ?? null;
	}

	public function getEntityId(): int
	{
		return (int)($this->data['ENTITY_ID'] ?? 0);
	}

	public function getHash(): string
	{
		return md5($this->hitId . $this->type . json_encode($this->data));
	}

	protected function prepareData(array $data = []): array
	{
		$validFields = [
			'GROUP_ID',
			'PREVIOUS_GROUP_ID',
			'NAME',
			'PROJECT_DATE_START',
			'PROJECT_DATE_FINISH',
			'IMAGE_ID',
			'AVATAR_TYPE',
			'OPENED',
			'CLOSED',
			'VISIBLE',
			'PROJECT',
			'KEYWORDS',
			'USER_ID',
			'TITLE',
			'RECEPIENTS',
			'SONET_LOG_ID',
			'SONET_LOG_COMMENT_ID',
			'FEATURE_ID',
			'SPACE_ID',
			'TYPE_ID',
			'ENTITY_ID',
			'ENTITY_TYPE_ID',
			'ID',
			'ATTENDEES_CODES',
			'EVENT_ID',
			'ROLE',
			'OLD_ROLE',
			'NEW_ROLE',
			'INITIATED_BY_TYPE',
			'OLD_INITIATED_BY_TYPE',
			'LOG_RIGHTS',
			'LOG_RIGHTS_BEFORE_UPDATE',
			'OLD_MEMBERS',
			'NEW_MEMBERS',
			'ATTENDEES_BEFORE_UPDATE',
			'ATTENDEES_AFTER_UPDATE',
			'ATTENDEES_CODES_BEFORE_UPDATE',
			'ATTENDEES_CODES_AFTER_UPDATE',
			'COMMENT_ID',
			'MESSAGE_ID',
		];

		if (!empty($data['TASK_ID']))
		{
			$data['ID'] = $data['TASK_ID'];
		}

		if (is_array($data['NEW_RECORD'] ?? null))
		{
			$data = array_merge($data, $data['NEW_RECORD']);
		}

		foreach ($data as $key => $row)
		{
			if (!in_array($key, $validFields, true))
			{
				unset($data[$key]);
			}
		}

		return $data;
	}

	public function getRecepients(): Collector
	{
		$eventType = $this->getType();
		$data = $this->getData();

		switch ($eventType)
		{
			case EventDictionary::EVENT_SPACE_LIVEFEED_POST_VIEW:
				return new RecepientCollection(...[new Recepient($data['USER_ID'])]);
			case EventDictionary::EVENT_SPACE_LIVEFEED_POST_ADD:
			case EventDictionary::EVENT_SPACE_LIVEFEED_POST_UPD:
			case EventDictionary::EVENT_SPACE_LIVEFEED_POST_DEL:
			case EventDictionary::EVENT_SPACE_LIVEFEED_COMMENT_ADD:
			case EventDictionary::EVENT_SPACE_LIVEFEED_COMMENT_UPD:
			case EventDictionary::EVENT_SPACE_LIVEFEED_COMMENT_DEL:
				return new SonetRightsRecepient($data['SONET_LOG_ID'], $data['LOG_RIGHTS'] ?? null);
			case EventDictionary::EVENT_WORKGROUP_USER_ADD:
			case EventDictionary::EVENT_WORKGROUP_USER_UPDATE:
			case EventDictionary::EVENT_WORKGROUP_USER_DELETE:
				return new WorkgroupRequestRecipient($data['GROUP_ID']);
			default:
				// in case recipient ids are defined
				if (isset($data['RECEPIENTS']) && is_array($data['RECEPIENTS']))
				{
					$recipients = [];
					foreach ($data['RECEPIENTS'] as $id)
					{
						$recipients[] = new Recepient($id);
					}
					return new RecepientCollection(...$recipients);
				}

				// in case there is one recipient
				if (isset($data['USER_ID']))
				{
					return new RecepientCollection(...[new Recepient($data['USER_ID'])]);
				}
		}

		return new RecepientCollection(...[]);
	}

	public function collectNewData(): void
	{
		return;
	}

	public function getOldFields(): array
	{
		return [];
	}

	public function getNewFields(): array
	{
		return [];
	}
}
