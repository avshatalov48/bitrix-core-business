<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2020 Bitrix
 */
namespace Bitrix\Socialnetwork\Helper;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Gratitude
{
	const SOCIALNETWORK_GRATITUDE_IBLOCK_TYPE_ID = 'structure';
	const SOCIALNETWORK_GRATITUDE_IBLOCK_CODE = 'honour';
	const SOCIALNETWORK_GRATITUDE_PROPERTY_CODE = 'GRATITUDE';

	public static function getPropertyData()
	{
		static $result = null;

		if ($result === null)
		{
			$result = [];

			if (!Loader::includeModule('iblock'))
			{
				return $result;
			}

			$res = \Bitrix\Iblock\PropertyEnumerationTable::getList(array(
				'select' => [ 'ID', 'VALUE', 'SORT', 'XML_ID' ],
				'filter' => [
					'=PROPERTY.IBLOCK_ID' => self::getIblockId(),
					'=PROPERTY.CODE' => self::SOCIALNETWORK_GRATITUDE_PROPERTY_CODE
				],
				'order' => [ 'SORT' => 'ASC' ]
			));
			while ($enumFields = $res->fetch())
			{
				$result[$enumFields['XML_ID']] = $enumFields;
			}
		}

		return $result;
	}

	public static function getIblockId()
	{
		static $result = null;

		if ($result === null)
		{
			$result = false;

			if (!Loader::includeModule('iblock'))
			{
				return $result;
			}

			$res = \Bitrix\Iblock\IblockTable::getList(array(
				'filter' => [
					'=CODE' => self::SOCIALNETWORK_GRATITUDE_IBLOCK_CODE,
					'=IBLOCK_TYPE_ID' => self::SOCIALNETWORK_GRATITUDE_IBLOCK_TYPE_ID
				],
				'select' => [ 'ID' ]
			));
			if ($iblockFields = $res->fetch())
			{
				$result = (int)($iblockFields['ID']);
			}
		}

		return $result;
	}

	public static function create(array $params = [])
	{
		global $CACHE_MANAGER;

		$result = null;

		if (!Loader::includeModule('iblock'))
		{
			return $result;
		}

		$medal = (!empty($params['medal']) ? trim($params['medal']) : '');
		$employees = (is_array($params['employees']) && !empty($params['employees']) ? $params['employees'] : []);
		if (
			$medal === ''
			|| empty($employees)
		)
		{
			return $result;
		}

		$gratitudesIblockId = \Bitrix\Socialnetwork\Component\LogList\Gratitude::getGratitudesIblockId();
		if (!$gratitudesIblockId)
		{
			return $result;
		}

		$gratitudesPropertyData = self::getPropertyData();
		if (!array_key_exists($medal, $gratitudesPropertyData))
		{
			return $result;
		}

		$gratitudeEnumFields = $gratitudesPropertyData[$medal];

		$gratitudeElement = new \CIBlockElement;
		$result = $gratitudeElement->add(
			[
				'IBLOCK_ID' => $gratitudesIblockId,
				'DATE_ACTIVE_FROM' => new \Bitrix\Main\Type\DateTime(),
				'NAME' => str_replace('#GRAT_NAME#', $gratitudeEnumFields['VALUE'], Loc::getMessage('SOCIALNETWORK_HELPER_GRATITUDE_IBLOCKELEMENT_NAME'))
			],
			false,
			false
		);

		if ($result)
		{
			\CIBlockElement::setPropertyValuesEx(
				$result,
				$gratitudesIblockId,
				[
					'USERS' => $employees,
					self::SOCIALNETWORK_GRATITUDE_PROPERTY_CODE => [ 'VALUE' => $gratitudeEnumFields['ID'] ]
				]
			);

			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				foreach($employees as $employeeId)
				{
					$CACHE_MANAGER->clearByTag('BLOG_POST_GRATITUDE_TO_USER_'.$employeeId);
				}
			}
		}

		return $result;
	}
}
?>