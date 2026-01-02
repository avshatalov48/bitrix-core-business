<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto;

use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Main\Web\Json;

class EventListResponse
{
	/**
	 * @var EventResponse[]
	 */
	private array $items = [];

	/**
	 * @throws DtoValidationException
	 */
	public function __construct(
		public readonly string $etag,
		public readonly ?string $nextSyncToken,
		public readonly ?string $nextPageToken,
		array $items
	)
	{
		if (empty($this->nextSyncToken) && empty($this->nextPageToken))
		{
			throw new DtoValidationException(
				'Field "nextSyncToken" or "nextPageToken" should be not empty in EventListResponse DTO'
			);
		}

		foreach ($items as $item)
		{
			$this->items[] = new EventResponse(
				$item['id'],
				$item['etag'],
				$item['status'],
				$item['iCalUID'],
				$item['sequence'] ?? 0,
				$item['start'],
				$item['end'],
				$item['reminders'] ?? [],
				$item['originalStartTime'] ?? null,
				$item['summary'], // can be empty?
				$item['description'] ?? null,
				$item['location'] ?? null,
				$item['colorId'] ?? null,
				$item['recurrence'] ?? null,
				$item['recurringEventId'] ?? null,
				$item['transparency'] ?? null,
				$item['visibility'] ?? null,
				$item['attendees'] ?? null,
				$item['extendedProperties'] ?? null,
			);
		}
	}

	public static function fromJson(string $jsonResponse): self
	{
		$data = Json::decode($jsonResponse);

		$required = ['kind', 'etag', 'items'];

		foreach ($required as $key)
		{
			if (!isset($data[$key]))
			{
				throw new DtoValidationException(sprintf('Field "%s" is required in EventListResponse DTO', $key));
			}
		}

		if ($data['kind'] !== 'calendar#events')
		{
			throw new DtoValidationException('Data type should be "calendar#events"');
		}

		return new self(
			$data['etag'],
			$data['nextSyncToken'] ?? null,
			$data['nextPageToken'] ?? null,
			$data['items']
		);
	}

	public function getItems(): array
	{
		return $this->items;
	}
}
