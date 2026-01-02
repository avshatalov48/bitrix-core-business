<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Integration\Dav\Service;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use CDavXmlDocument;

class XmlDocument
{
	/**
	 * @throws LoaderException
	 * @throws SystemException
	 */
	public static function loadFromString(string $xml): CDavXmlDocument
	{
		if (!self::isAvailable())
		{
			throw new SystemException('Module dav is not installed');
		}

		return CDavXmlDocument::loadFromString($xml);
	}

	/**
	 * @throws LoaderException
	 */
	private static function isAvailable(): bool
	{
		return Loader::includeModule('dav');
	}
}
