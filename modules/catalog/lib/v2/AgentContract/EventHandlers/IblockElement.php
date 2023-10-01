<?php

namespace Bitrix\Catalog\v2\AgentContract\EventHandlers;

use Bitrix\Main;
use Bitrix\Catalog;
use Bitrix\Iblock;

class IblockElement
{
	public static function onBeforeIBlockElementDelete($productId)
	{
		global $APPLICATION;

		$productId = (int)$productId;
		if ($productId >= 0)
		{
			$agentProduct = Catalog\AgentProductTable::getRow([
				'select' => [
					'ID',
				],
				'filter' => [
					'=PRODUCT_ID' => $productId,
				],
			]);
			if ($agentProduct)
			{
				Main\Loader::includeModule('iblock');

				$element = Iblock\ElementTable::getList([
					'select' => ['ID', 'NAME'],
					'filter' => ['=ID' => $productId],
				])->fetch();

				$error = Main\Localization\Loc::getMessage(
					'CATALOG_AGENT_CONTRACT_ERROR_ELEMENT_IN_DOCUMENT_EXISTS',
					[
						'#ID#' => $element['ID'],
						'#NAME#' => $element['NAME'],
					]
				);
				$APPLICATION->ThrowException($error);

				return false;
			}
		}

		return true;
	}
}