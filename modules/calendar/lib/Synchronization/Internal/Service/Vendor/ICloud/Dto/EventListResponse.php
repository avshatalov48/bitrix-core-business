<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Dto;

use Bitrix\Calendar\Integration\Dav\Service\ICloud\Dto\XmlDataExtractorTrait;
use Bitrix\Calendar\Integration\Dav\Service\XmlDocument;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;

class EventListResponse
{
	use XmlDataExtractorTrait;

	private const CALENDAR_CONTENT_TYPE = 'httpd/unix-directory';

	/**
	 * @var EventResponse[]
	 */
	private array $items = [];

	/**
	 * @throws DtoValidationException
	 */
	public function __construct(
		public ?string $etag,
		public ?string $nextSyncToken,
		array $items,
	)
	{
		foreach ($items as $item)
		{
			$this->items[] = new EventResponse(
				$item['href'],
				$item['getetag'] ?? null,
				$item['status'],
				$item['calendar-data'] ?? [],
				$item['excluded-calendar-data'] ?? [],
			);
		}
	}

	/**
	 * @throws DtoValidationException
	 */
	public static function fromXml(string $xmlResponse): self
	{
		$items = [];
		$etag = null;

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

			$contentType = $data['getcontenttype'] ?? null;

			if ($contentType !== self::CALENDAR_CONTENT_TYPE)
			{
				$items[] = $data;

				continue;
			}

			$etag = $data['getetag'];
		}

		$syncTokenNode = $xmlDocument->getPath('/*/sync-token')[0] ?? null;
		$syncToken = $syncTokenNode?->getContent();

		return new self($etag, $syncToken, $items);
	}

	public function getItems(): array
	{
		return $this->items;
	}

	private static function getRequiredProperties(): array
	{
		return ['href', 'status'];
	}

	private static function getResourceType(): ?string
	{
		return null;
	}
}
