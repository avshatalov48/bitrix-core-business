<?php
/**
* Bitrix Framework
* @package bitrix
* @subpackage socialnetwork
* @copyright 2001-2017 Bitrix
*/
namespace Bitrix\Socialnetwork\Integration\Intranet\Absence;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

class User
{
	public static function getVacationList(): array
	{
		global $CACHE_MANAGER;

		$result = [];

		$cacheTTL = 3600 * 24 * 30;
		$cacheDir = '/sonet/user_absence';

		$cache = new \CPHPCache();
		if ($cache->initCache($cacheTTL, 'intranet_absence', $cacheDir))
		{
			$result = $cache->getVars();
		}
		else
		{
			$cache->startDataCache();
			$CACHE_MANAGER->startTagCache($cacheDir);

			if (
				ModuleManager::isModuleInstalled('intranet')
				&& Loader::includeModule('iblock')
				&& ($absenceIblockId = (int)Option::get('intranet', 'iblock_absence'))
			)
			{
				$CACHE_MANAGER->registerTag('iblock_id_'.$absenceIblockId);

				$res = \CIBlockProperty::getList(
					[],
					[
						'IBLOCK_ID' => $absenceIblockId,
						'ACTIVE' => 'Y',
						'CODE' => 'ABSENCE_TYPE',
					]
				);

				if (
					($property = $res->fetch())
					&& ($absenceTypePropertyId = (int)$property['ID'])
				)
				{
					$vacationXMLIdList = [];
					$res = \CIBlockPropertyEnum::getList(
						[],
						[
							'PROPERTY_ID' => $absenceTypePropertyId,
							'XML_ID' => [ 'VACATION', 'LEAVEMATERINITY' ],
						]
					);

					while ($enum = $res->fetch())
					{
						$vacationXMLIdList[$enum['XML_ID']] = (int)$enum['ID'];
					}

					if (isset($vacationXMLIdList['LEAVEMATERINITY']))
					{
						$result = array_merge($result, self::getVacationListOfType([
							'absenceIblockId' => $absenceIblockId,
							'absenceTypeId' => $vacationXMLIdList['LEAVEMATERINITY'],
						]));
					}

					if (isset($vacationXMLIdList['VACATION']))
					{
						$result = array_merge($result, self::getVacationListOfType([
							'absenceIblockId' => $absenceIblockId,
							'absenceTypeId' => $vacationXMLIdList['VACATION'],
							'fromTimestamp' => time() - (3600 * 24 * 120),
						]));
					}
				}
			}

			$CACHE_MANAGER->endTagCache();
			$cache->endDataCache($result);
		}

		return $result;
	}

	private static function getVacationListOfType(array $params = []): array
	{
		$result = [];

		$absenceIblockId = (int)($params['absenceIblockId'] ?? 0);
		$absenceTypeId = (int)($params['absenceTypeId'] ?? 0);
		$fromTimestamp = ($params['fromTimestamp'] ?? false);

		if (
			$absenceIblockId <= 0
			|| $absenceTypeId <= 0
			|| !Loader::includeModule('iblock')
		)
		{
			return $result;
		}

		$filter = [
			'IBLOCK_ID' => $absenceIblockId,
			'PROPERTY_ABSENCE_TYPE' => $absenceTypeId,
			'ACTIVE' => 'Y',
		];

		if ($fromTimestamp)
		{
			$filter['>=DATE_ACTIVE_FROM'] = Date(\Bitrix\Main\Type\Date::convertFormatToPhp(FORMAT_DATETIME), $fromTimestamp);
		}

		$res = \CIBlockElement::getList(
			[],
			$filter,
			false,
			false,
			[ 'ID', 'DATE_ACTIVE_FROM', 'DATE_ACTIVE_TO', 'PROPERTY_ABSENCE_TYPE', 'PROPERTY_USER' ]
		);

		while ($absence = $res->fetch())
		{
			$result[] = [
				'USER_ID' => $absence['PROPERTY_USER_VALUE'],
				'DATE_FROM' =>  $absence['DATE_ACTIVE_FROM'],
				'DATE_TO' =>  $absence['DATE_ACTIVE_TO'],
				'ABSENCE_TYPE' =>  $absence['PROPERTY_ABSENCE_TYPE_ENUM_ID'],
			];
		}

		return $result;
	}

	public static function getDayVacationList($params = array()): array
	{
		$result = [];

		$userList = (isset($params['userList']) && is_array($params['userList']) ? $params['userList'] : []);
		$vacationList = self::getVacationList();

		if (empty($vacationList))
		{
			return $result;
		}

		$ts = time();

		foreach ($vacationList as $vacation)
		{
			if (
				!empty($userList)
				&& !in_array($vacation['USER_ID'], $userList)
			)
			{
				continue;
			}

			$vacationTSStart = makeTimeStamp($vacation['DATE_FROM'], FORMAT_DATETIME);

			if ($vacationTSStart < $ts)
			{
				$vacationTSFinish = makeTimeStamp($vacation['DATE_TO'], FORMAT_DATETIME) + 86400;

				if ($vacationTSFinish > $ts)
				{
					$result[$vacation['USER_ID']] = $vacation['ABSENCE_TYPE'];
				}
			}
		}

		return $result;
	}
}
