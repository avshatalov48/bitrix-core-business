<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto;

use Bitrix\Calendar\Sync\Office365\Dto\DateTimeDto;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;

/**
 * @link https://learn.microsoft.com/en-us/graph/api/resources/event?view=graph-rest-1.0#properties
 */
class EventResponse
{
	public function __construct(
		public readonly string $id,
		public readonly string $etag,
		public readonly ?string $version,
		public readonly ?string $recurrence,
		public readonly DateTimeDto $start,
		public readonly array $customData = [],
	)
	{
	}

	/**
	 * @throws DtoValidationException
	 */
	public static function fromArray(array $data): self
	{
		$required = ['id', '@odata.etag'];

		foreach ($required as $key)
		{
			if (empty($data[$key]))
			{
				throw new DtoValidationException(sprintf('Field "%s" is required in CalendarResponse DTO', $key));
			}
		}

		return new self(
			$data['id'],
			$data['@odata.etag'],
			$data['changeKey'] ?? null,
			$data['seriesMasterId'] ?? null,
			new DateTimeDto($item['start'] ?? []),
			static::prepareCustomData($data),
		);
	}

	private static function prepareCustomData(array $data): array
	{
		$customData = [];

		if (!empty($data['location']))
		{
			$customData['location'] = $data['location'];
		}

		if (!empty($data['locations']))
		{
			foreach ($data['locations'] as $location)
			{
				$customData['locations'][] = $location;
			}
		}

		if (!empty($data['attendees']))
		{
			$customData['attendees'] = [];

			foreach ($data['attendees'] as $attendee)
			{
				$customData['attendees'][] = $attendee;
			}
		}

		return $customData;
	}
}
