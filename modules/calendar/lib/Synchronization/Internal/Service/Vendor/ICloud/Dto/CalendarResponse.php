<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Dto;

use Bitrix\Calendar\Integration\Dav\Service\ICloud\Dto\XmlDataExtractorTrait;
use Bitrix\Calendar\Integration\Dav\Service\XmlDocument;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;

class CalendarResponse
{
	use XmlDataExtractorTrait;

	public function __construct(public readonly string $id, public readonly string $etag)
	{
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

	private static function getRequiredProperties(): array
	{
		return ['href', 'getetag'];
	}

	private static function getResourceType(): string
	{
		return 'calendar';
	}
}
