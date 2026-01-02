<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto;

use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Web\Json;

class CalendarListResponse
{
	/**
	 * @var CalendarListEntryResponse[]
	 */
	private array $items = [];

	/**
	 * @throws DtoValidationException
	 */
	public function __construct(public readonly string $etag, public readonly string $nextSyncToken, array $items)
	{
		foreach ($items as $item)
		{
			// https://developers.google.com/calendar/api/v3/reference/calendarList#resource-representations
			// Don't trust the doc!
			// If the calendar was deleted in Google, the response will return the fields:
			// kind, etag, id, defaultReminders, deleted

			$this->items[] = new CalendarListEntryResponse(
				$item['etag'],
				$item['id'],
				$item['summary'] ?? null,
				$item['description'] ?? null,
				$item['backgroundColor'] ?? null,
				$item['accessRole'] ?? null,
				$item['primary'] ?? false,
				$item['deleted'] ?? false,
			);
		}
	}

	/**
	 * @param string $jsonResponse
	 *
	 * @return self
	 *
	 * @throws DtoValidationException
	 */
	public static function fromJson(string $jsonResponse): self
	{
		try
		{
			$data = Json::decode($jsonResponse);
		}
		catch (ArgumentException $e)
		{
			throw new DtoValidationException(
				sprintf('Wrong JSON from Google: "%s"', $e->getMessage()),
				previous: $e
			);
		}

		$required = ['etag', 'kind', 'nextSyncToken', 'items'];

		foreach ($required as $key)
		{
			if (!isset($data[$key]))
			{
				throw new DtoValidationException(sprintf('Field "%s" is required in CalendarListResponse DTO', $key));
			}
		}

		if ($data['kind'] !== 'calendar#calendarList')
		{
			throw new DtoValidationException('Data type should be "calendar#calendarList"');
		}

		return new self($data['etag'], $data['nextSyncToken'], $data['items']);
	}

	/**
	 * @return CalendarListEntryResponse[]
	 */
	public function getItems(): array
	{
		return $this->items;
	}

	/**
	 * @return string[]
	 */
	public function getItemsIds(): array
	{
		return array_map(fn(CalendarListEntryResponse $response) => $response->id, $this->items);
	}
}
