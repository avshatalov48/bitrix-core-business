<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto;

use Bitrix\Calendar\Sync\Google\Dictionary;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Web\Json;

/**
 * @link https://developers.google.com/calendar/api/v3/reference/events#resource-representations
 */
class EventResponse
{
	private EventDateResponse $start;
	private EventDateResponse $end;
	private ?EventDateResponse $originalStartTime = null;

	/**
	 * @throws DtoValidationException
	 */
	public function __construct(
		public readonly string $id,
		public readonly string $etag,
		public readonly string $status,
		public readonly string $iCalUID,
		public readonly int $sequence = 0,
		array $start,
		array $end,
		public readonly array $reminders,
		?array $originalStartTime,
		public readonly ?string $summary,
		public readonly ?string $description,
		public readonly ?string $location,
		public readonly ?string $colorId,
		public readonly ?array $recurrence, // string[]
		public readonly ?string $recurringEventId = null,
		public readonly ?string $transparency,
		public readonly ?string $visibility,
		public readonly ?array $attendees,
		public readonly ?array $extendedProperties
	)
	{
		$this->validate();

		$this->start = $this->buildEventDate($start);
		$this->end = $this->buildEventDate($end);

		if ($originalStartTime)
		{
			$this->originalStartTime = $this->buildEventDate($originalStartTime);
		}
	}

	/**
	 * @throws DtoValidationException
	 */
	private function validate(): void
	{
		if (!in_array($this->status, ['confirmed', 'tentative', 'cancelled'], true))
		{
			throw new DtoValidationException('Value "' . $this->status . '" is not a valid status');
		}

		if ($this->transparency && !in_array($this->transparency, ['opaque', 'transparent'], true))
		{
			throw new DtoValidationException(
				'Value "' . $this->transparency . '" is not a valid value of transparency'
			);
		}

		if ($this->visibility && !in_array($this->visibility, ['default', 'public', 'private', 'confidential'], true))
		{
			throw new DtoValidationException('Value "' . $this->visibility . '" is not a valid value of visibility');
		}
	}

	/**
	 * @throws DtoValidationException
	 */
	private function buildEventDate(array $data): EventDateResponse
	{
		return new EventDateResponse(
			$data['date'] ?? null,
			$data['dateTime'] ?? null,
			$data['timeZone'] ?? null
		);
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

		$required = ['id', 'etag', 'kind'];

		foreach ($required as $key)
		{
			if (empty($data[$key]))
			{
				throw new DtoValidationException(sprintf('Field "%s" is required in EventResponse DTO', $key));
			}
		}

		if ($data['kind'] !== 'calendar#event')
		{
			throw new DtoValidationException('Data type should be "calendar#event"');
		}

		return new self(
			$data['id'],
			$data['etag'],
			$data['status'],
			$data['iCalUID'],
			$data['sequence'] ?? 0,
			$data['start'],
			$data['end'],
			$data['reminders'],
			$data['originalStartTime'] ?? null,
			$data['summary'] ?? null,
			$data['description'] ?? null,
			$data['location'] ?? null,
			$data['colorId'] ?? null,
			$data['recurrence'] ?? null,
			$data['recurringEventId'] ?? null,
			$data['transparency'] ?? null,
			$data['visibility'] ?? null,
			$data['attendees'] ?? null,
			$data['extendedProperties'] ?? null,
		);
	}

	public function getVersion(): ?string
	{
		return $this->etag;
	}

	public function getStart(): EventDateResponse
	{
		return $this->start;
	}

	public function getEnd(): EventDateResponse
	{
		return $this->end;
	}

	public function getOriginalStartTime(): ?EventDateResponse
	{
		return $this->originalStartTime;
	}

	public function isInstance(): bool
	{
		return $this->recurringEventId && $this->getOriginalStartTime() !== null;
	}

	public function getAction(): ?string
	{
		return Dictionary::SYNC_ACTION[$this->status] ?? null;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'etag' => $this->etag,
			'status' => $this->status,
			'iCalUID' => $this->iCalUID,
			'sequence' => $this->sequence,
			'start' => $this->start->toArray(),
			'end' => $this->end->toArray(),
			'reminders' => $this->reminders,
			'originalStartTime' => $this->originalStartTime?->toArray(),
			'summary' => $this->summary,
			'description' => $this->description,
			'location' => $this->location,
			'colorId' => $this->colorId,
			'recurrence' => $this->recurrence,
			'recurringEventId' => $this->recurringEventId,
			'transparency' => $this->transparency,
			'visibility' => $this->visibility,
			'attendees' => $this->attendees,
			'extendedProperties' => $this->extendedProperties,
		];
	}
}
