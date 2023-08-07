<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\RoundingTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Result;

final class Enum extends Controller
{
	/** @deprecated  */
	public const PROPERTY_USER_TYPE_DATETIME = PropertyTable::USER_TYPE_DATETIME;
	public const PROPERTY_USER_TYPE_MONEY = 'Money';
	/** @deprecated  */
	public const PROPERTY_USER_TYPE_SKU = PropertyTable::USER_TYPE_SKU;
	public const PROPERTY_USER_TYPE_BOOL_ENUM = 'BoolEnum';

	public function getProductTypesAction(): array
	{
		$r = [];
		$list = ProductTable::getProductTypes(true);

		foreach($list as $id=>$name)
		{
			$r[] = ['ID'=>$id, 'NAME'=>$name];
		}

		return ['ENUM'=>$r];
	}

	public function getRoundTypesAction(): array
	{
		$r = [];
		$list = RoundingTable::getRoundTypes(true);

		foreach($list as $id=>$name)
		{
			$r[] = ['ID'=>$id, 'NAME'=>$name];
		}

		return ['ENUM'=>$r];
	}

	/**
	 * @return array
	 */
	public function getStoreDocumentTypesAction(): array
	{
		$result = [];
		foreach (Document::getAvailableRestDocumentTypes() as $id=>$name)
		{
			$result[] = [
				'ID' => $id,
				'NAME' => $name,
			];
		}

		return ['ENUM' => $result];
	}

	public function getProductPropertyTypesAction(): array
	{
		return [
			'ENUM' => self::getProductPropertyTypes(),
		];
	}

	public static function getProductPropertyTypes(): array
	{
		return [
			'NUMBER' => [
				'PROPERTY_TYPE' => PropertyTable::TYPE_NUMBER,
				'USER_TYPE' => null,
			],
			'STRING' => [
				'PROPERTY_TYPE' => PropertyTable::TYPE_STRING,
				'USER_TYPE' => null,
			],
			'LIST' => [
				'PROPERTY_TYPE' => PropertyTable::TYPE_LIST,
				'USER_TYPE' => null,
			],
			'BOOL_ENUM' => [
				'PROPERTY_TYPE' => PropertyTable::TYPE_LIST,
				'USER_TYPE' => self::PROPERTY_USER_TYPE_BOOL_ENUM,
			],
			'DATETIME' => [
				'PROPERTY_TYPE' => PropertyTable::TYPE_STRING,
				'USER_TYPE' => PropertyTable::USER_TYPE_DATETIME,
			],
			'MONEY' => [
				'PROPERTY_TYPE' => PropertyTable::TYPE_STRING,
				'USER_TYPE' => self::PROPERTY_USER_TYPE_MONEY,
			],
			'SKU' => [
				'PROPERTY_TYPE' => PropertyTable::TYPE_ELEMENT,
				'USER_TYPE' => PropertyTable::USER_TYPE_SKU,
			],
		];
	}

	protected function checkPermissionEntity($name, $arguments=[])
	{
		return new Result();
	}
}
