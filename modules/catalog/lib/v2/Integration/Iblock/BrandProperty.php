<?php

namespace Bitrix\Catalog\v2\Integration\Iblock;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\StringHelper;

final class BrandProperty
{
	public const PROPERTY_CODE = 'BRAND_FOR_FACEBOOK';

	public static function createFacebookBrandProperty(): void
	{
		if (!Loader::includeModule('crm'))
		{
			return;
		}

		$crmCatalogIblockId = \Bitrix\Crm\Product\Catalog::getDefaultId();

		if (!Loader::includeModule('iblock') || !self::checkIblock($crmCatalogIblockId))
		{
			return;
		}

		$propertyId = self::loadProperty($crmCatalogIblockId);
		if (!$propertyId)
		{
			self::createProperty($crmCatalogIblockId);
		}
	}

	private static function checkIblock(int $iblockId): bool
	{
		$iblock = IblockTable::getList([
			'select' => ['ID'],
			'filter' => ['=ID' => $iblockId],
		])
			->fetch()
		;

		return !empty($iblock);
	}

	private static function loadProperty(int $iblockId): ?int
	{
		$property = PropertyTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=IBLOCK_ID' => $iblockId,
				'=CODE' => self::PROPERTY_CODE,
				'=ACTIVE' => 'Y',
			],
		])
			->fetch()
		;
		if (!empty($property))
		{
			return (int)$property['ID'];
		}

		return null;
	}

	private static function createProperty(int $iblockId): ?int
	{
		$highloadBlockTable = self::createHighloadBlockTable();
		if (!$highloadBlockTable)
		{
			return null;
		}

		$propertyDescription = self::getPropertyDescription($iblockId, $highloadBlockTable);
		$propertyId = (new \CIBlockProperty())->Add($propertyDescription);

		return (int)$propertyId ?: null;
	}

	private static function getPropertyDescription(int $iblockId, string $highloadBlockTable): array
	{
		return [
			'NAME' => Loc::getMessage('CATALOG_BRAND_PROPERTY_NAME'),
			'CODE' => self::PROPERTY_CODE,
			'XML_ID' => self::PROPERTY_CODE,
			'IBLOCK_ID' => $iblockId,
			'PROPERTY_TYPE' => PropertyTable::TYPE_STRING,
			'USER_TYPE' => 'directory',
			'USER_TYPE_SETTINGS' => [
				'size' => 1,
				'width' => 0,
				'group' => 'N',
				'multiple' => 'N',
				'TABLE_NAME' => $highloadBlockTable,
			],
			'MULTIPLE' => 'Y',
			'MULTIPLE_CNT' => 1,
			'WITH_DESCRIPTION' => 'N',
			'SORT' => 200,
		];
	}

	private static function createHighloadBlockTable(): ?string
	{
		if (!Loader::includeModule('highloadblock'))
		{
			return null;
		}

		$uniqId = uniqid('BRAND_FOR_FACEBOOK_', false);
		$className = StringHelper::snake2camel($uniqId);
		$tableName = mb_strtolower(\CIBlockPropertyDirectory::createHighloadTableName($uniqId));

		$addResult = HighloadBlockTable::add([
			'NAME' => $className,
			'TABLE_NAME' => $tableName,
		]);
		if (!$addResult->isSuccess())
		{
			return null;
		}

		$userFieldEntity = new \CUserTypeEntity();

		foreach (self::getUserFieldDescriptions((string)$addResult->getId()) as $description)
		{
			$userFieldEntity->Add($description);
		}

		return $tableName;
	}

	private static function getUserFieldDescriptions(string $highloadBlockId): array
	{
		return [
			[
				'ENTITY_ID' => 'HLBLOCK_' . $highloadBlockId,
				'FIELD_NAME' => 'UF_NAME',
				'USER_TYPE_ID' => 'string',
				'XML_ID' => 'UF_BRAND_NAME',
				'SORT' => '100',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'Y',
			],
			[
				'ENTITY_ID' => 'HLBLOCK_' . $highloadBlockId,
				'FIELD_NAME' => 'UF_FILE',
				'USER_TYPE_ID' => 'file',
				'XML_ID' => 'UF_BRAND_FILE',
				'SORT' => '200',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'Y',
			],
			[
				'ENTITY_ID' => 'HLBLOCK_' . $highloadBlockId,
				'FIELD_NAME' => 'UF_LINK',
				'USER_TYPE_ID' => 'string',
				'XML_ID' => 'UF_BRAND_LINK',
				'SORT' => '300',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'Y',
			],
			[
				'ENTITY_ID' => 'HLBLOCK_' . $highloadBlockId,
				'FIELD_NAME' => 'UF_DESCRIPTION',
				'USER_TYPE_ID' => 'string',
				'XML_ID' => 'UF_BRAND_DESCR',
				'SORT' => '400',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'Y',
			],
			[
				'ENTITY_ID' => 'HLBLOCK_' . $highloadBlockId,
				'FIELD_NAME' => 'UF_FULL_DESCRIPTION',
				'USER_TYPE_ID' => 'string',
				'XML_ID' => 'UF_BRAND_FULL_DESCR',
				'SORT' => '500',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'Y',
			],
			[
				'ENTITY_ID' => 'HLBLOCK_' . $highloadBlockId,
				'FIELD_NAME' => 'UF_SORT',
				'USER_TYPE_ID' => 'double',
				'XML_ID' => 'UF_BRAND_SORT',
				'SORT' => '600',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'N',
			],
			[
				'ENTITY_ID' => 'HLBLOCK_' . $highloadBlockId,
				'FIELD_NAME' => 'UF_EXTERNAL_CODE',
				'USER_TYPE_ID' => 'string',
				'XML_ID' => 'UF_BRAND_EXTERNAL_CODE',
				'SORT' => '700',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'N',
			],
			[
				'ENTITY_ID' => 'HLBLOCK_' . $highloadBlockId,
				'FIELD_NAME' => 'UF_XML_ID',
				'USER_TYPE_ID' => 'string',
				'XML_ID' => 'UF_XML_ID',
				'SORT' => '800',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'Y',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'N',
			],
		];
	}
}
