<?php

namespace Bitrix\Catalog\UserField;

use Bitrix\Catalog\Controller\Controller;
use Bitrix\Main\Loader;

class UserFieldAccess extends \Bitrix\Main\UserField\UserFieldAccess
{
	protected function getAvailableEntityIds(): array
	{
		return array_map(fn($item): string => 'IBLOCK_'.$item.'_SECTION', static::getIBlockList());
	}

	protected static function getIBlockList(): array
	{
		Loader::includeModule('catalog');
		Loader::includeModule('iblock');

		$list = [];
		$arFilterTmp = [];

		$r = \CCatalog::GetList();
		while ($l = $r->fetch())
		{
			$arFilterTmp['ID'][] = $l['IBLOCK_ID'];
		}

		$arFilterTmp['ACTIVE'] = 'Y';
		$arFilterTmp['OPERATION'] = Controller::IBLOCK_EDIT;

		$dbIBlock = \CIBlock::GetList(Array("ID" => "ASC"), $arFilterTmp);
		while ($arIBlock = $dbIBlock->Fetch())
		{
			$list[] =  (int)$arIBlock["ID"];
		}

		return $list;
	}

	public function getRestrictedTypes(): array
	{
		return array_merge(parent::getRestrictedTypes(), [
			'video',
			'vote',
			'url_preview',
			'string_formatted',
			'disk_file',
			'disk_version',
		]);
	}
}