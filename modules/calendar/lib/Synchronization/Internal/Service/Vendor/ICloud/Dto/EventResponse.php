<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Dto;

// TODO: Remove API from sync when we switch to the new one
use Bitrix\Calendar\Core\Event\Properties\ExcludedDatesCollection;
use Bitrix\Calendar\Integration\Dav\Service\ICloud\Dto\XmlDataExtractorTrait;
use Bitrix\Calendar\Integration\Dav\Service\XmlDocument;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Util;
use Bitrix\Main\IO\Path;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\SystemException;
use CCalendar;

class EventResponse
{
	use XmlDataExtractorTrait;

	private const TIME_LIMIT = 2600000;
	private const ACTIONS = [
		200 => Dictionary::SYNC_EVENT_ACTION['success'],
		404 => Dictionary::SYNC_EVENT_ACTION['delete'],
	];

	public readonly string $id;

	private ?array $overriddenInstancesData = null;

	/**
	 * @throws DtoValidationException
	 */
	public function __construct(
		public readonly string $href,
		public readonly ?string $etag,
		public readonly int $status = 200,
		public readonly array $eventData = [],
		private array $excludedEventsData = [],
	)
	{
		if (!in_array($this->status, [200, 404], true))
		{
			throw new DtoValidationException('Value "' . $this->status . '" is not a valid status');
		}

		if ($this->status === 200 && empty($this->etag))
		{
			throw new DtoValidationException('Field "getetag" is required if the event exists');
		}

		$this->id = $this->getIdByPath($href);

		foreach ($this->excludedEventsData as $key => $excludedEventData)
		{
			$this->excludedEventsData[$key]['RECURRING_EVENT_ID'] = $this->id;
		}
	}

	/**
	 * @throws DtoValidationException
	 */
	public static function fromXml(string $xmlResponse): self
	{
		try
		{
			$xmlDocument = XmlDocument::loadFromString($xmlResponse);
		}
		catch (LoaderException|SystemException)
		{
			throw new DtoValidationException(
				sprintf('Failed to load XML document from response in %s DTO', self::getClassName()),
			);
		}

		$response = $xmlDocument->getPath('/*/response')[0] ?? null;
		if ($response === null)
		{
			throw new DtoValidationException('Response is not valid');
		}

		$data = self::getData($response);

		return new self($data['href'], $data['getetag']);
	}

	/**
	 * @throws ObjectException
	 */
	public function getOverriddenInstancesData(): array
	{
		if ($this->overriddenInstancesData !== null)
		{
			return $this->overriddenInstancesData;
		}

		$instancesData = $this->excludedEventsData;

		$deletedInstancesDates = $this->eventData['EXDATE'] ?? [];

		$excludeDeletedInstancesCallback = static function (array $instanceData) use ($deletedInstancesDates): bool {
			$excludedDate = Util::getDateObject($instanceData['RECURRENCE_ID_DATE'] ?? null);

			$formattedExcludedDate = $excludedDate->format(ExcludedDatesCollection::EXCLUDED_DATE_FORMAT);

			$isDeletedInstance = in_array($formattedExcludedDate, $deletedInstancesDates, true);

			return !$isDeletedInstance;
		};

		$this->overriddenInstancesData = array_filter($instancesData, $excludeDeletedInstancesCallback);

		return $this->overriddenInstancesData;
	}

	public function getVersion(): ?string
	{
		return $this->etag;
	}

	public function getAction(): string
	{
		return self::ACTIONS[$this->status];
	}

	public function isEmpty(): bool
	{
		return empty($this->eventData);
	}

	public function isOld(): bool
	{
		$limitTimestamp = time() - self::TIME_LIMIT;

		$eventData = $this->eventData;

		$periodUntil = $eventData['PROPERTY_PERIOD_UNTIL'] ?? null;

		$dateTo = $eventData['DATE_TO'] ?? null;

		if (!empty($periodUntil))
		{
			if ((int)CCalendar::timestamp($periodUntil) - $limitTimestamp < 0)
			{
				return true;
			}
		}
		elseif (
			!empty($dateTo)
			&& (int)CCalendar::timestamp($dateTo) - $limitTimestamp < 0
		)
		{
			return true;
		}

		return false;
	}

	private function getIdByPath(string $path): string
	{
		$eventName = Path::getName($path);

		$dotPosition = mb_strrpos($eventName, '.');

		if ($dotPosition === false)
		{
			return $eventName;
		}

		return substr($eventName, 0, $dotPosition);
	}

	private static function getRequiredProperties(): array
	{
		return ['href', 'getetag'];
	}

	private static function getResourceType(): ?string
	{
		return null;
	}
}
