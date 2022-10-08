<?php
namespace Bitrix\Catalog\Helpers\Admin;

use Bitrix\Catalog\RoundingTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class RoundEdit
 * Provides various useful methods for admin pages.
 *
 * @package Bitrix\Catalog\Helper\Admin
 */
class RoundEdit
{
	/**
	 * Return default round values for admin forms.
	 *
	 * @param bool $dropdownList		Return list for usage in admin pages.
	 * @return array
	 */
	public static function getPresetRoundValues($dropdownList = false): array
	{
		$result = RoundingTable::getPresetRoundingValues();
		if (!$dropdownList)
		{
			return $result;
		}

		$list = [];
		foreach ($result as $value)
		{
			$value = (string)$value;
			$list[$value] = $value;
		}

		return $list;
	}

	/**
	 * Prepare admin form data.
	 *
	 * @param array &$fields		Fields for update/add.
	 * @return void
	 */
	public static function prepareFields(array &$fields): void
	{
		if (isset($fields['ROUND_TYPE']))
		{
			$fields['ROUND_TYPE'] = (int)$fields['ROUND_TYPE'];
		}
	}
}
