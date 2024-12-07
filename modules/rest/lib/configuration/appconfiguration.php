<?php

namespace Bitrix\Rest\Configuration;

use Bitrix\Main\Localization\Loc;
use Bitrix\Rest\AppLogTable;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\EventTable;
use Bitrix\Rest\Event\Sender;
use Bitrix\Main\Event;
use Bitrix\Rest\Marketplace\Application;

class AppConfiguration
{
	public const REST_APPLICATION = 'REST_APPLICATION';
	private static $entityList = [
		self::REST_APPLICATION => 100,
	];

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
		if (
			!static::$entityList[$code]
			|| !Manifest::isEntityAvailable($code, $event->getParameters(), static::$accessManifest)
		)
		{
			return $result;
		}

		if (static::checkRequiredParams($code))
		{
			$step = $event->getParameter('STEP');
			$setting = $event->getParameter('SETTING');
			switch ($code)
			{
				case 'REST_APPLICATION':
					$result = static::exportApp($step, $setting);
					break;
			}
		}

		return $result;
	}

	public static function onEventClearController(Event $event)
	{
	}

	public static function onEventImportController(Event $event)
	{
		$result = null;
		if (!static::checkAccessImport($event))
		{
			return $result;
		}

		$code = $event->getParameter('CODE');
		if (static::checkRequiredParams($code))
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

	private static function checkAccessImport(Event $event)
	{
		$code = $event->getParameter('CODE');
		if (
			!isset(static::$entityList[$code])
			|| !static::$entityList[$code]
			|| !Manifest::isEntityAvailable($code, $event->getParameters(), static::$accessManifest)
		)
		{
			return false;
		}

		return true;
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
		if (!empty($item['CONTENT']['DATA']['code']))
		{
			$code = preg_replace('/[^a-zA-Z0-9._\-]/', '', $item['CONTENT']['DATA']['code']);
			if ($code !== $item['CONTENT']['DATA']['code'])
			{
				return [
					'ERROR_EXCEPTION' => [
						'message' => Loc::getMessage(
							'REST_CONFIGURATION_ERROR_UNKNOWN_APP'
						),
					],
				];
			}

			Application::setContextUserId((int)$item['USER_ID']);
			$result = Application::install($code);
			if ($result['success'])
			{
				$res = AppTable::getList(
					[
						'filter' => [
							'=CODE' => $code,
							'=ACTIVE' => AppTable::ACTIVE,
							'=INSTALLED' => AppTable::NOT_INSTALLED,
							'!=URL_INSTALL' => false,
						],
						'limit' => 1
					]
				);
				if ($app = $res->fetch())
				{
					$res = EventTable::getList(
						[
							'filter' => [
								"APP_ID" => $app['ID'],
								"EVENT_NAME" => "ONAPPINSTALL",
								"EVENT_HANDLER" => $app["URL_INSTALL"],
							],
							'limit' => 1
						]
					);
					if (!$event = $res->fetch())
					{
						$res = EventTable::add(
							[
								"APP_ID" => $app['ID'],
								"EVENT_NAME" => "ONAPPINSTALL",
								"EVENT_HANDLER" => $app["URL_INSTALL"],
							]
						);
						if ($res->isSuccess())
						{
							Sender::bind('rest', 'OnRestAppInstall');
						}

						AppTable::setSkipRemoteUpdate(true);
						AppTable::update(
							$app['ID'],
							[
								'INSTALLED' => AppTable::INSTALLED
							]
						);
						AppTable::setSkipRemoteUpdate(false);

						AppLogTable::log($app['ID'], AppLogTable::ACTION_TYPE_INSTALL);

						AppTable::install($app['ID']);
					}
				}
			}
			elseif (is_array($result) && $result['errorDescription'])
			{
				$result['ERROR_EXCEPTION']['message'] = Loc::getMessage(
					'REST_CONFIGURATION_ERROR_INSTALL_APP_CONTENT_DATA',
					[
						'#ERROR_CODE#' => $result['error'],
						'#ERROR_MESSAGE#' => $result['errorDescription'],
					]
				);
			}
			else
			{
				$result['ERROR_EXCEPTION']['message'] = Loc::getMessage(
					'REST_CONFIGURATION_ERROR_INSTALL_APP_CONTENT'
				);
			}
		}
		else
		{
			return [
				'ERROR_EXCEPTION' => [
					'message' => Loc::getMessage(
						'REST_CONFIGURATION_ERROR_UNKNOWN_APP'
					),
				],
			];
		}

		return $result;
	}

	private static function clearApp($option)
	{
		$result = [
			'NEXT' => false
		];
		if ($option['CLEAR_FULL'])
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

			while ($appInfo = $dbRes->Fetch())
			{
				$result['NEXT'] = $appInfo['ID'];

				$currentApp = Helper::getInstance()->getContextAction($appInfo['ID']);
				if (!empty($option['CONTEXT']) && $option['CONTEXT'] === $currentApp)
				{
					continue;
				}

				$checkResult = AppTable::checkUninstallAvailability($appInfo['ID']);
				if ($checkResult->isEmpty())
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

	public static function exportApp($step, $setting)
	{
		$return = [
			'FILE_NAME' => '',
			'CONTENT' => [],
			'NEXT' => false
		];

		$filter = [
			'!=STATUS' => AppTable::STATUS_LOCAL,
			'=ACTIVE' => AppTable::ACTIVE,
		];

		if (is_array($setting))
		{
			if (!empty($setting['APP_REQUIRED']) && is_array($setting['APP_REQUIRED']))
			{
				$filter = [
					[
						'LOGIC' => 'OR',
						$filter,
						[
							'=ID' => $setting['APP_REQUIRED'],
						],
					],
				];
			}
			elseif (array_key_exists('APP_USES_REQUIRED', $setting))
			{
				$filter = [];
				if (!empty($setting['APP_USES_REQUIRED']))
				{
					$filter = [
						'=ID' => $setting['APP_USES_REQUIRED'],
					];
				}
			}
		}

		if (!empty($filter))
		{
			$res = AppTable::getList(
				[
					'order' => [
						'ID' => 'ASC',
					],
					'filter' => $filter,
					'select' => [
						'ID',
						'CODE',
					],
					'limit' => 1,
					'offset' => $step,
				]
			);

			if ($app = $res->Fetch())
			{
				$return['FILE_NAME'] = $step;
				$return['NEXT'] = $step;
				$return['CONTENT'] = [
					'code' => $app['CODE'],
					'settings' => [],
				];
			}
		}

		return $return;
	}
	//end region application
}
