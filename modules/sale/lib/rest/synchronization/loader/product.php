<?php


namespace Bitrix\Sale\Rest\Synchronization\Loader;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Loader;
use Bitrix\Sale\Registry;

class Product extends Entity
{
	public function getFieldsByExternalId($code)
	{
		$result = array();

		Loader::includeModule('iblock');
		Loader::includeModule('catalog');

		$iblockIds = [];
		$row = \Bitrix\Catalog\CatalogIblockTable::getList([
			'select' => ['IBLOCK_ID'],
			'filter' => ['=IBLOCK.ACTIVE'=>'Y']
		]);
		while ($res = $row->fetch())
			$iblockIds[] = $res['IBLOCK_ID'];

		//TODO: необходимо переделать на вызов метода каталога, который на вход полчучает произвольный product_xml_id и возвращает продукт каталога.
		if (empty($iblockIds))
		{
				// nothing here
		}
		else
		{
			$r = \CIBlockElement::GetList(array(),
				array("=XML_ID" => $code, "ACTIVE" => "Y", "CHECK_PERMISSIONS" => "Y", "IBLOCK_ID"=>$iblockIds),
				false,
				false,
				array("ID", "IBLOCK_ID", "XML_ID", "NAME", "DETAIL_PAGE_URL")
			);
			if($ar = $r->GetNext())
			{
				$result = $ar;
				$product = \CCatalogProduct::GetByID($ar["ID"]);

				$result["WEIGHT"] = $product["WEIGHT"];
				$result["CATALOG_GROUP_NAME"] = $product["CATALOG_GROUP_NAME"];

				$productIBlock = static::getIBlockProduct($ar["IBLOCK_ID"]);
				$result["IBLOCK_XML_ID"] = $productIBlock[$ar["IBLOCK_ID"]]["XML_ID"];
			}
		}

		return $result;
	}

	public function getCodeAfterDelimiter($code)
	{
		$result = '';

		if(mb_strpos($code, '#') !== false)
		{
			$code = explode('#', $code);
			$result = $code[1];
		}
		return $result;
	}

	private static function getIBlockProduct($iblockId)
	{
		static $iblock_fields = null;

		if($iblock_fields[$iblockId] == null)
		{
			$r = \CIBlock::GetList(array(), array("ID" => $iblockId));
			if ($ar = $r->Fetch())
				$iblock_fields[$iblockId] = $ar;
		}
		return $iblock_fields;
	}
}