<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Dto;

use Bitrix\Calendar\Integration\Dav\Service\ICloud\Dto\XmlDataExtractorTrait;
use Bitrix\Calendar\Integration\Dav\Service\XmlDocument;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;

final class SyncTokenResponse
{
	use XmlDataExtractorTrait;

	public function __construct(
		public ?string $etag,
		public ?string $nextSyncToken,
	)
	{}

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
			throw new DtoValidationException('Failed to load XML document from response in SyncTokenResponse DTO');
		}

		$response = $xmlDocument->getPath('/*/response')[0] ?? null;
		if ($response === null)
		{
			throw new DtoValidationException('Response is not valid');
		}

		$data = self::getData($response);

		if (empty($data['sync-token']))
		{
			$syncTokenNode = $xmlDocument->getPath('/*/sync-token')[0] ?? null;
			$data['sync-token'] = $syncTokenNode?->getContent();
		}

		return new self($data['getetag'], $data['sync-token']);
	}

	private static function getRequiredProperties(): array
	{
		return ['getetag', 'sync-token'];
	}

	private static function getResourceType(): ?string
	{
		return null;
	}
}
