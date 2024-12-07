<?php

namespace Bitrix\Catalog\v2\Integration\Seo\Facebook;

use Bitrix\Highloadblock;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use CLanguage;
use CUserTypeEntity;

class FacebookBrandPropertyCreator
{
	private const BRAND_REFERENCE_NAME = 'FacebookBrandReference';
	private const BRAND_REFERENCE_TABLE_NAME = 'b_catalog_facebook_brand_reference';
	private const BRAND_PROPERTY_CODE = 'BRAND_FOR_FACEBOOK';

	public static function createFacebookBrandProperty(): void
	{
		self::createProperty();
		self::createHighLoadBlock();
	}

	private static function createProperty(): void
	{
		if (
			!Loader::includeModule('crm')
			|| !Loader::includeModule('iblock')
		)
		{
			return;
		}

		$crmCatalogIblockId = \Bitrix\Crm\Product\Catalog::getDefaultId();
		$facebookBrandProperty = \CIBlockPropertyTools::getExistProperty(
			$crmCatalogIblockId,
			self::BRAND_PROPERTY_CODE
		);
		if ($facebookBrandProperty)
		{
			return;
		}
		\CIBlockPropertyTools::createProperty(
			$crmCatalogIblockId,
			self::BRAND_PROPERTY_CODE,
		);
	}

	private static function createHighLoadBlock(): void
	{
		if (!Loader::includeModule('highloadblock'))
		{
			return;
		}

		$highLoadBlockResult = Highloadblock\HighloadBlockTable::getList([
			'filter' => [
				'NAME' => self::BRAND_REFERENCE_NAME,
				'TABLE_NAME' => self::BRAND_REFERENCE_TABLE_NAME,
			],
			'cache' => [
				'ttl' => 86400,
			],
		]);

		if ($highLoadBlockResult->fetch())
		{
			return;
		}

		$data = [
			'NAME' => self::BRAND_REFERENCE_NAME,
			'TABLE_NAME' => self::BRAND_REFERENCE_TABLE_NAME,
		];

		$result = Highloadblock\HighloadBlockTable::add($data);
		if (!$result->isSuccess())
		{
			return;
		}

		$highLoadBlockId = $result->getId();
		$highLoadData = Highloadblock\HighloadBlockTable::getById($highLoadBlockId)->fetch();
		Highloadblock\HighloadBlockTable::compileEntity($highLoadData);
		$userFields = [
			[
				'ENTITY_ID' => 'HLBLOCK_' . $highLoadBlockId,
				'FIELD_NAME' => 'UF_NAME',
				'USER_TYPE_ID' => 'string',
				'XML_ID' => 'UF_BRAND_FOR_FACEBOOK_NAME',
				'SORT' => '100',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'Y',
			],
			[
				'ENTITY_ID' => 'HLBLOCK_' . $highLoadBlockId,
				'FIELD_NAME' => 'UF_FILE',
				'USER_TYPE_ID' => 'file',
				'XML_ID' => 'UF_BRAND_FOR_FACEBOOK_FILE',
				'SORT' => '200',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'Y',
			],
			[
				'ENTITY_ID' => 'HLBLOCK_' . $highLoadBlockId,
				'FIELD_NAME' => 'UF_LINK',
				'USER_TYPE_ID' => 'string',
				'XML_ID' => 'UF_BRAND_FOR_FACEBOOK_LINK',
				'SORT' => '300',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'Y',
			],
			[
				'ENTITY_ID' => 'HLBLOCK_' . $highLoadBlockId,
				'FIELD_NAME' => 'UF_DESCRIPTION',
				'USER_TYPE_ID' => 'string',
				'XML_ID' => 'UF_BRAND_FOR_FACEBOOK_DESCR',
				'SORT' => '400',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'Y',
			],
			[
				'ENTITY_ID' => 'HLBLOCK_' . $highLoadBlockId,
				'FIELD_NAME' => 'UF_FULL_DESCRIPTION',
				'USER_TYPE_ID' => 'string',
				'XML_ID' => 'UF_BRAND_FOR_FACEBOOK_FULL_DESCR',
				'SORT' => '500',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'Y',
			],
			[
				'ENTITY_ID' => 'HLBLOCK_' . $highLoadBlockId,
				'FIELD_NAME' => 'UF_SORT',
				'USER_TYPE_ID' => 'double',
				'XML_ID' => 'UF_BRAND_FOR_FACEBOOK_SORT',
				'SORT' => '600',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'N',
			],
			[
				'ENTITY_ID' => 'HLBLOCK_' . $highLoadBlockId,
				'FIELD_NAME' => 'UF_EXTERNAL_CODE',
				'USER_TYPE_ID' => 'string',
				'XML_ID' => 'UF_BRAND_FOR_FACEBOOK_EXTERNAL_CODE',
				'SORT' => '700',
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'N',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'N',
			],
			[
				'ENTITY_ID' => 'HLBLOCK_' . $highLoadBlockId,
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

		$languageIds = [];
		$languageResult = CLanguage::GetList();
		while($language = $languageResult->Fetch())
		{
			$languageIds[] = $language['LID'];
		}

		$userTypeEntity  = new CUserTypeEntity;
		foreach ($userFields as $userField)
		{
			$userTypeResult = CUserTypeEntity::GetList(
				[],
				[
					'ENTITY_ID' => $userField['ENTITY_ID'],
					'FIELD_NAME' => $userField['FIELD_NAME'],
				]
			);
			if ($userTypeResult->Fetch())
			{
				continue;
			}

			$labelNames = [];
			foreach($languageIds as $languageId)
			{
				$messages = Loc::loadLanguageFile(__FILE__, $languageId);
				$labelNames[$languageId] = $messages[$userField['FIELD_NAME']];
			}

			$userField['EDIT_FORM_LABEL'] = $labelNames;
			$userField['LIST_COLUMN_LABEL'] = $labelNames;
			$userField['LIST_FILTER_LABEL'] = $labelNames;

			$userTypeEntity->Add($userField);
		}
	}
}
