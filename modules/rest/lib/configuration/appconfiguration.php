<?php

namespace Bitrix\Rest\Configuration;

use Bitrix\Rest\AppLogTable;
use Bitrix\Rest\AppTable;
use Bitrix\Main\Event;
use CRestUtil;

class AppConfiguration
{
	private static $entityList = [
		'REST_APPLICATION' => 100,
	];
	private static $code;
	private static $accessManifest = [
		'total',
		'app'
	];

	public static function getEntityList()
	{
		return static::$entityList;
	}

	public static function getManifestList(Event $event)
	{
		$manifestList = [];
		$manifestList[] = [
			'CODE' => 'total',
			'VERSION' => 1,
			'ACTIVE' => 'N',
			'PLACEMENT' => [],
			'USES' => [
				'total'
			]
		];

		return $manifestList;
	}

	public static function onEventExportController(Event $event)
	{
		$result = null;
		$code = $event->getParameter('CODE');
		if(!static::$entityList[$code])
		{
			return $result;
		}

		$manifest = $event->getParameter('MANIFEST');
		$access = array_intersect($manifest['USES'], static::$accessManifest);
		if(!$access)
		{
			return $result;
		}
		static::$code = $code;

		if(static::checkRequiredParams($code))
		{
			$step = $event->getParameter('STEP');
			switch ($code)
			{
				case 'REST_APPLICATION':
					$result = static::exportApp($step);
					break;
			}
		}

		return $result;
	}

	public static function onEventClearController(Event $event)
	{
		$code = $event->getParameter('CODE');
		if(!static::$entityList[$code])
		{
			return null;
		}
		$result = null;
		static::$code = $code;

		if(static::checkRequiredParams($code))
		{
			$option = $event->getParameters();
			switch ($code)
			{
				case 'REST_APPLICATION':
					$result = static::clearApp($option);
					break;
			}
		}

		return $result;
	}

	public static function onEventImportController(Event $event)
	{
		$code = $event->getParameter('CODE');
		if(!static::$entityList[$code])
		{
			return null;
		}
		$result = null;
		static::$code = $code;

		if(static::checkRequiredParams($code))
		{
			$data = $event->getParameters();
			switch ($code)
			{
				case 'REST_APPLICATION':
					$result = static::importApp($data);
					break;
			}
		}

		return $result;
	}

	/**
	 *
	 * @param $type string of event
	 * @return boolean
	 */
	private static function checkRequiredParams($type)
	{
		return true;
	}

	//region application
	private static function importApp($item)
	{
		$result = false;
		if(!empty($item['CONTENT']['DATA']['code']))
		{
			$type = AppTable::getAppType($item['CONTENT']['DATA']['code']);
			if($type != AppTable::TYPE_CONFIGURATION)
			{
				$result = CRestUtil::InstallApp($item['CONTENT']['DATA']['code']);
			}
		}
		return $result;
	}

	private static function clearApp($option)
	{
		$result = [
			'NEXT' => false
		];
		if($option['CLEAR_FULL'])
		{
			$dbRes = AppTable::getList(
				[
					'order' => [
						'ID' => 'ASC'
					],
					'filter' => [
						'=ACTIVE' => AppTable::ACTIVE,
						"!=STATUS" => AppTable::STATUS_LOCAL,
						'>ID' => $option['NEXT']
					],
					'limit' => 5
				]
			);
			while($appInfo = $dbRes->Fetch())
			{
				$result['NEXT'] = $appInfo['ID'];

				$checkResult = AppTable::checkUninstallAvailability($appInfo['ID']);
				if($checkResult->isEmpty() && AppTable::canUninstallByType($appInfo['CODE'], $appInfo['VERSION']))
				{
					AppTable::uninstall($appInfo['ID']);
					$appFields = [
						'ACTIVE' => 'N',
						'INSTALLED' => 'N',
					];
					AppTable::update($appInfo['ID'], $appFields);
					AppLogTable::log($appInfo['ID'], AppLogTable::ACTION_TYPE_UNINSTALL);
				}
			}
		}

		return $result;
	}

	public static function exportApp($step)
	{
		$return = [
			'FILE_NAME' => '',
			'CONTENT' => [],
			'NEXT' => false
		];

		$res = AppTable::getList(
			[
				'order' => [
					'ID' => 'ASC'
				],
				'filter' => [
					'!=STATUS' => AppTable::STATUS_LOCAL,
					'=ACTIVE' => AppTable::ACTIVE,
				],
				'select' => [
					'CODE'
				],
				'limit' => 1,
				'offset' => $step
			]
		);

		if($app = $res->Fetch())
		{
			$return['FILE_NAME'] = $step;
			$return['NEXT'] = $step;
			$return['CONTENT'] = [
				'code' => $app['CODE'],
				'settings' => []
			];
		}
		return $return;
	}
	//end region application
}