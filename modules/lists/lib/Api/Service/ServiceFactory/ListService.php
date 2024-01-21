<?php

namespace Bitrix\Lists\Api\Service\ServiceFactory;

use Bitrix\Lists\Api\Data\IBlockService\IBlockElementFilter;
use Bitrix\Lists\Api\Data\IBlockService\IBlockListFilter;

final class ListService extends ServiceFactory
{
	private static string $iBlockTypeId = 'lists';

	public static function getIBlockTypeId(): string
	{
		return self::$iBlockTypeId;
	}

	protected function fillCatalogFilter(IBlockListFilter $filter): void
	{
		$filter->setSite(SITE_ID);
	}

	protected function fillElementListFilter(IBlockElementFilter $filter): void
	{}

	protected function fillElementDetailInfoFilter(IBlockElementFilter $filter): void
	{}
}
