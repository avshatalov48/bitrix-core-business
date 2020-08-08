<?php


namespace Bitrix\Main\Access\Auth;


use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Access\AccessCode;
use Bitrix\Main\Loader;
use Bitrix\Main\UserAccessTable;

class AccessEventHandler
{
	public static function onBeforeIBlockSectionUpdate(&$fields)
	{
		if (
			!is_array($fields)
			|| !array_key_exists('IBLOCK_ID', $fields)
			|| !array_key_exists('UF_HEAD', $fields)
			|| !array_key_exists('ID', $fields)
		)
		{
			return;
		}

		$iblockId = (int) \COption::GetOptionInt('intranet', 'iblock_structure');
		if (
			!$iblockId
			|| (int) $fields['IBLOCK_ID'] !== $iblockId
		)
		{
			return;
		}

		$ufHead = (int) $fields['UF_HEAD'];
		if ($ufHead > 0)
		{
			(new AccessAuthProvider())->DeleteByUser($ufHead);
		}

		$accessCode = AccessCode::ACCESS_DIRECTOR . $fields['ID'];
		self::deleteByAccessCode($accessCode);
	}

	public static function onBeforeIBlockSectionDelete($sectionId)
	{
		$sectionId = (int) $sectionId;
		if ($sectionId < 1)
		{
			return;
		}

		$iblockId = (int) \COption::GetOptionInt('intranet', 'iblock_structure');
		if (!$iblockId)
		{
			return;
		}

		if (!Loader::includeModule('iblock'))
		{
			return;
		}

		$res = SectionTable::getList([
			'filter' => [
				'=IBLOCK_ID' => $iblockId,
				'=ID' => $sectionId
			]
		]);
		if(!$res->getSelectedRowsCount())
		{
			return;
		}

		$accessCode = AccessCode::ACCESS_DIRECTOR . $sectionId;
		self::deleteByAccessCode($accessCode);
	}

	public static function onBeforeIBlockSectionAdd(&$fields)
	{
		if (
			!is_array($fields)
			|| !array_key_exists('IBLOCK_ID', $fields)
			|| !array_key_exists('UF_HEAD', $fields)
			|| !array_key_exists('IBLOCK_SECTION_ID', $fields)
		)
		{
			return;
		}

		$ufHead = (int) $fields['UF_HEAD'];
		if ($ufHead < 1)
		{
			return;
		}
		(new AccessAuthProvider())->DeleteByUser($ufHead);
	}

	private static function deleteByAccessCode(string $accessCode)
	{
		// find users by access codes
		$res = UserAccessTable::getList([
			'filter' => [
				'=ACCESS_CODE' => $accessCode
			],
			'select' => ['USER_ID']
		]);

		$provider = new AccessAuthProvider();
		while ($row = $res->fetch())
		{
			$provider->DeleteByUser($row['USER_ID']);
		}
	}
}