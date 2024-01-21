<?php

namespace Bitrix\Calendar\Sharing\Util;

use Bitrix\Calendar\Internals\SharingLinkTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

final class ExpiredLinkCleanAgent
{
	private const MAX_LINKS_PER_QUERY = 500;

	/**
	 * runs agent that deactivate expired sharing links
	 * @return string
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function runAgent(): string
	{
		(new self())->run();

		return self::class . "::runAgent();";
	}

	/**
	 * @return void
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function run(): void
	{
		$expiredLinks = $this->getExpiredLinks();
		if (!empty($expiredLinks))
		{
			$this->cleanExpiredLinks($expiredLinks);
		}
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getExpiredLinks(): array
	{
		$expiredLinks =
			SharingLinkTable::query()
				->setSelect(['ID'])
				->whereNotNull('DATE_EXPIRE')
				->where('DATE_EXPIRE', '<', new DateTime())
				->where('ACTIVE', '=', 'Y')
				->setLimit(self::MAX_LINKS_PER_QUERY)
				->fetchAll()
		;

		return array_map(static function($expiredLink){
			return (int)$expiredLink['ID'];
		}, $expiredLinks);
	}

	/**
	 * @param array $expiredLinks
	 * @return void
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	private function cleanExpiredLinks(array $expiredLinks): void
	{
		SharingLinkTable::updateMulti(
			$expiredLinks,
			['ACTIVE' => 'N'],
		);
	}
}