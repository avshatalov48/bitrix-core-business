<?php
namespace Bitrix\Iblock\Update;

use Bitrix\Crm\Product\Catalog;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\FullIndex\FullText;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Update\Stepper;

class IblockElementFulltextIndex extends Stepper
{
	protected const ELEMENT_LIMIT = 50;

	protected static $moduleId = 'iblock';

	public function execute(array &$option): bool
	{
		$option["count"] ??= ElementTable::getCount([
			'IBLOCK.FULLTEXT_INDEX' => 'Y',
		]);
		$option["steps"] ??= 0;
		$option["lastElementId"] ??= 0;
		$option["iblockIds"] ??= $this->prepareIblockIds();

		if (empty($option["iblockIds"]))
		{
			return self::FINISH_EXECUTION;
		}

		$elementData = ElementTable::getList([
			'limit' => self::ELEMENT_LIMIT,
			'select' => ['ID', 'IBLOCK_ID', 'SEARCHABLE_CONTENT'],
			'filter' => [
				'>ID' => (int)$option["lastElementId"],
				'@IBLOCK_ID' => $option["iblockIds"],
			],
			'order' => ['ID' => 'ASC'],
		]);

		$elementCount = 0;
		while ($element = $elementData->fetch())
		{
			$elementId = $element["ID"];
			$iblockId = $element['IBLOCK_ID'];
			$searchableContent = $element['SEARCHABLE_CONTENT'];
			$option["lastElementId"] = $elementId;

			$searchIndexParams = [
				'ELEMENT_ID' => $elementId,
				'SEARCH_CONTENT' => $searchableContent,
			];

			FullText::update($iblockId, $elementId, $searchIndexParams);

			$elementCount++;
		}

		$option["steps"] += $elementCount;

		return $elementCount === 0 ? self::FINISH_EXECUTION : self::CONTINUE_EXECUTION;
	}

	public static function getTitle(): string
	{
		return Loc::getMessage('IBLOCK_ELEMENT_SEARCHABLE_CONTENT_TRANSFER');
	}

	private static function prepareIblockIds(): array
	{
		if (!Loader::includeModule('crm'))
		{
			return [];
		}

		$iblockIds = [];

		$catalogProductId = Catalog::getDefaultId();
		$obIBlock = new \CAllIBlock();

		if (isset($catalogProductId))
		{
			$obIBlock->Update($catalogProductId, ['FULLTEXT_INDEX' => 'Y']);
			$iblockIds[] = $catalogProductId;
		}

		$catalogOffersId = Catalog::getDefaultOfferId();

		if (isset($catalogOffersId))
		{
			$obIBlock->Update($catalogOffersId, ['FULLTEXT_INDEX' => 'Y']);
			$iblockIds[] = $catalogOffersId;
		}

		return $iblockIds;
	}
}