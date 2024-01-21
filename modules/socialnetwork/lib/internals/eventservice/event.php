<?php

namespace Bitrix\Socialnetwork\Internals\EventService;

use Bitrix\Socialnetwork\Internals\EventService\Recepients\Recepient;
use Bitrix\Socialnetwork\Internals\EventService\Recepients\RecepientCollection;
use Bitrix\Socialnetwork\Internals\EventService\Recepients\SonetRightsRecepient;

/**
 * Class Event
 *
 * @package Bitrix\Socialnetwork\Internals\EventService\Event
 */

class Event
{
	protected int|null $eventId = null;
	protected array $data = [];

	public function __construct(
		protected string $hitId,
		protected string $type = ''
	)
	{

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

	public function getHash(): string
	{
		return md5($this->hitId . $this->type . json_encode($this->data));
	}

	protected function prepareData(array $data = []): array
	{
		$validFields = [
			'GROUP_ID',
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
		];

		foreach ($data as $key => $row)
		{
			if (!in_array($key, $validFields, true))
			{
				unset($data[$key]);
			}
		}

		return $data;
	}

	public function getRecepients(): \Iterator
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
				return new SonetRightsRecepient($data['SONET_LOG_ID']);
			default:
				// in case recepient ids are defined
				if (isset($data['RECEPIENTS']) && is_array($data['RECEPIENTS']))
				{
					$recepients = [];
					foreach ($data['RECEPIENTS'] as $id)
					{
						$recepients[] = new Recepient($id);
					}
					return new RecepientCollection(...$recepients);
				}

				// in case there is one recepient
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
