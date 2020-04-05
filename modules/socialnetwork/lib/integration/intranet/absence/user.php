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
	public static function getVacationList()
	{
		global $CACHE_MANAGER;

		$result = array();

		$cacheTTL = 3600*24*30;
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
				&& ($absenceIblockId = intval(Option::get('intranet', 'iblock_absence')))
			)
			{
				$CACHE_MANAGER->registerTag('iblock_id_'.$absenceIblockId);

				$res = \CIBlockProperty::getList(
					array(),
					array(
						'IBLOCK_ID' => $absenceIblockId,
						'ACTIVE' => 'Y',
						'CODE' => 'ABSENCE_TYPE'
					)
				);

				if (
					($property = $res->fetch())
					&& ($absenceTypePropertyId = intval($property['ID']))
				)
				{
					$vacationXMLIdList = array();
					$res = \CIBlockPropertyEnum::getList(
						array(),
						array(
							'PROPERTY_ID' => $absenceTypePropertyId,
							'XML_ID' => array('VACATION', 'LEAVEMATERINITY')
						)
					);

					while($enum = $res->fetch())
					{
						$vacationXMLIdList[] = intval($enum['ID']);
					}

					if (!empty($vacationXMLIdList))
					{
						$filter = array(
							'IBLOCK_ID' => $absenceIblockId,
							'PROPERTY_ABSENCE_TYPE' => $vacationXMLIdList,
							'ACTIVE' => 'Y',
						);

						$res = \CIBlockElement::getList(
							array(),
							$filter,
							false,
							false,
							array('ID', 'DATE_ACTIVE_FROM', 'DATE_ACTIVE_TO', 'PROPERTY_ABSENCE_TYPE', 'PROPERTY_USER')
						);

						while ($absence = $res->fetch())
						{
							$result[$absence['PROPERTY_USER_VALUE']] = array(
								'USER_ID' => $absence['PROPERTY_USER_VALUE'],
								'DATE_FROM' =>  $absence['DATE_ACTIVE_FROM'],
								'DATE_TO' =>  $absence['DATE_ACTIVE_TO'],
								'ABSENCE_TYPE' =>  $absence['PROPERTY_ABSENCE_TYPE_ENUM_ID']
							);
						}
					}
				}
			}

			$CACHE_MANAGER->endTagCache();
			$cache->endDataCache($result);
		}

		return $result;
	}

	public static function getDayVacationList($params = array())
	{
		$result = array();
		$userList = (isset($params['userList']) && is_array($params['userList']) ? $params['userList'] : false);
		$vacationList = self::getVacationList();

		if (empty($vacationList))
		{
			return $result;
		}

		$ts = time();

		foreach($vacationList as $vacation)
		{
			if (
				$userList
				&& !in_array($vacation['ID'], $userList)
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
?>