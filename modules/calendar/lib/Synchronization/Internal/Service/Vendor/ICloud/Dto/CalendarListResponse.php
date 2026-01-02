<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Dto;

use Bitrix\Calendar\Integration\Dav\Service\ICloud\Dto\XmlDataExtractorTrait;
use Bitrix\Calendar\Integration\Dav\Service\XmlDocument;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;

class CalendarListResponse
{
	use XmlDataExtractorTrait;

	/**
	 * @var CalendarListEntryResponse[]
	 */
	private array $items = [];

	/**
	 * @throws DtoValidationException
	 */
	public function __construct(array $items)
	{
		foreach ($items as $item)
		{
			$this->items[] = new CalendarListEntryResponse(
				$item['href'],
				$item['getetag'],
				$item['displayname'],
				$item['calendar-description'] ?? null,
				$item['supported-calendar-component-set'] ?? null,
				$item['calendar-color'] ?? null,
			);
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

		$responses = $xmlDocument->getPath('/*/response');

		$items = [];

		foreach ($responses as $response)
		{
			try
			{
				$data = self::getData($response);
			}
			catch (DtoValidationException)
			{
				continue;
			}

			$items[] = $data;
		}

		return new self($items);
	}

	public function getItems(): array
	{
		return $this->items;
	}

	private static function getRequiredProperties(): array
	{
		return ['href', 'getetag', 'displayname'];
	}

	private static function getResourceType(): string
	{
		return 'calendar';
	}
}
