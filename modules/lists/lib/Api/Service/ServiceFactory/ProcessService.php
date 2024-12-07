<?php

namespace Bitrix\Lists\Api\Service\ServiceFactory;

use Bitrix\Lists\Api\Data\IBlockService\IBlockElementFilter;
use Bitrix\Lists\Api\Data\IBlockService\IBlockListFilter;
use Bitrix\Lists\Api\Response\ServiceFactory\GetCatalogResponse;
use Bitrix\Main\Config\Option;

final class ProcessService extends ServiceFactory
{
	private static string $iBlockTypeId = '';

	public static function getIBlockTypeId(): string
	{
		if (empty(self::$iBlockTypeId))
		{
			self::$iBlockTypeId = Option::get('lists', 'livefeed_iblock_type_id', 'bitrix_processes');
		}

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

	public function getAddElementLiveFeedCatalog(): GetCatalogResponse
	{
		$response = new GetCatalogResponse();

		$catalogResponse = $this->getAddElementCatalog();
		$response->fillFromResponse($catalogResponse);

		if ($response->isSuccess())
		{
			$catalog = [];
			foreach ($catalogResponse->getCatalog() as $iBlock)
			{
				if (\CLists::getLiveFeed($iBlock['ID']))
				{
					$catalog[] = $iBlock;
				}
			}

			$response->setCatalog($catalog);
		}

		return $response;
	}
}
