<?php

namespace Bitrix\Rest\Configuration;

use Bitrix\Main\EventManager;
use Bitrix\Main\Event;
use Bitrix\Rest\Configuration\Core\OwnerEntityTable;

class Controller
{
	const ON_REST_APP_CONFIGURATION_CLEAR = 'OnRestApplicationConfigurationClear';
	const ON_REST_APP_CONFIGURATION_ENTITY = 'OnRestApplicationConfigurationEntity';
	const ON_REST_APP_CONFIGURATION_EXPORT = 'OnRestApplicationConfigurationExport';
	const ON_REST_APP_CONFIGURATION_IMPORT = 'OnRestApplicationConfigurationImport';
	const ON_REST_APP_CONFIGURATION_FINISH = 'OnRestApplicationConfigurationFinish';

	/**
	 *	array value: [a-zA-Z0-9_]
	 */
	public static function getEntityCodeList()
	{
		$result = [];

		$event = new Event('rest', static::ON_REST_APP_CONFIGURATION_ENTITY);
		EventManager::getInstance()->send($event);
		foreach ($event->getResults() as $eventResult)
		{
			$codeList = $eventResult->getParameters();
			if (is_array($codeList))
			{
				$result = array_merge($result, $codeList);
			}
		}
		asort($result);

		return array_keys($result);
	}

	public static function callEventExport($manifestCode, $code, $step = 0, $next = '', $itemCode = '', $contextUser = false)
	{
		$result = [];
		if ($manifestCode == '')
		{
			return $result;
		}

		$manifest = Manifest::get($manifestCode);
		if (!is_null($manifest))
		{
			$setting = new Setting($contextUser);

			$event = new Event(
				'rest',
				static::ON_REST_APP_CONFIGURATION_EXPORT,
				[
					'CODE' => $code,
					'STEP' => $step,
					'NEXT' => $next,
					'MANIFEST' => $manifest,
					'ITEM_CODE' => $itemCode,
					'SETTING' => $setting->get(Setting::SETTING_MANIFEST),
					'USER_ID' => $setting->get(Setting::SETTING_USER_ID) ?? 0,
				]
			);
			EventManager::getInstance()->send($event);
			foreach ($event->getResults() as $eventResult)
			{
				$parameters = $eventResult->getParameters();
				$result[] = [
					'FILE_NAME' => $parameters['FILE_NAME'],
					'CONTENT' => $parameters['CONTENT'],
					'FILES' => $parameters['FILES'],
					'NEXT' => $parameters['NEXT'],
					'ERROR_MESSAGES' => $parameters['ERROR_MESSAGES'],
					'ERROR_ACTION' => $parameters['ERROR_ACTION']
				];
			}
		}

		return $result;
	}

	public static function callEventClear($data)
	{
		$result = [
			'NEXT' => false
		];

		$data['SETTING'] = null;
		if (isset($data['CONTEXT_USER']))
		{
			$setting = new Setting($data['CONTEXT_USER']);
			$data['SETTING'] = $setting->get(Setting::SETTING_MANIFEST);
			$data['USER_ID'] = $setting->get(Setting::SETTING_USER_ID) ?? 0;
		}

		$event = new Event(
			'rest',
			static::ON_REST_APP_CONFIGURATION_CLEAR,
			$data
		);
		EventManager::getInstance()->send($event);
		foreach ($event->getResults() as $eventResult)
		{
			$parameters = $eventResult->getParameters();
			$result = [
				'NEXT' => $parameters['NEXT'],
				'ERROR_MESSAGES' => $parameters['ERROR_MESSAGES'],
				'ERROR_ACTION' => $parameters['ERROR_ACTION'],
				'ERROR_EXCEPTION' => $parameters['ERROR_EXCEPTION']
			];

			if (is_array($parameters['OWNER_DELETE']))
			{
				OwnerEntityTable::deleteMulti($parameters['OWNER_DELETE']);
			}
		}

		return $result;
	}

	public static function callEventImport($params)
	{
		$result = [];
		$params['CONTEXT_USER'] = $params['CONTEXT_USER'] ?: false;
		$setting = new Setting($params['CONTEXT_USER']);

		$app = $setting->get(Setting::SETTING_APP_INFO);
		if ($app['ID'] > 0)
		{
			$owner = $app['ID'];
			$ownerType = OwnerEntityTable::ENTITY_TYPE_APPLICATION;
		}
		else
		{
			$owner = OwnerEntityTable::ENTITY_EMPTY;
			$ownerType = OwnerEntityTable::ENTITY_TYPE_EXTERNAL;
		}

		$event = new Event(
			'rest',
			static::ON_REST_APP_CONFIGURATION_IMPORT,
			[
				'CODE' => $params['CODE'],
				'CONTENT' => $params['CONTENT'],
				'RATIO' => $params['RATIO'],
				'CONTEXT' => $params['CONTEXT'],
				'CONTEXT_USER' => $params['CONTEXT_USER'],
				'SETTING' => $setting->get(Setting::SETTING_MANIFEST),
				'USER_ID' => $setting->get(Setting::SETTING_USER_ID) ?? 0,
				'MANIFEST_CODE' => $params['MANIFEST_CODE'],
				'IMPORT_MANIFEST' => $params['IMPORT_MANIFEST'],
				'ADDITIONAL_OPTION' => is_array($params['ADDITIONAL_OPTION']) ? $params['ADDITIONAL_OPTION'] : [],
				'APP_ID' => intVal($owner),
			]
		);

		EventManager::getInstance()->send($event);
		foreach ($event->getResults() as $eventResult)
		{
			$parameters = $eventResult->getParameters();
			$result[] = [
				'RATIO' => $parameters['RATIO'],
				'ERROR_MESSAGES' => $parameters['ERROR_MESSAGES'],
				'ERROR_ACTION' => $parameters['ERROR_ACTION'],
				'ERROR_EXCEPTION' => $parameters['ERROR_EXCEPTION']
			];

			if (is_array($parameters['OWNER_DELETE']))
			{
				OwnerEntityTable::deleteMulti($parameters['OWNER_DELETE']);
			}

			if ($parameters['OWNER'])
			{
				OwnerEntityTable::saveMulti($owner, $ownerType, $parameters['OWNER']);
			}
		}

		return $result;
	}

	public static function callEventFinish($params)
	{
		$result = [];
		$event = new Event(
			'rest',
			static::ON_REST_APP_CONFIGURATION_FINISH,
			$params
		);
		EventManager::getInstance()->send($event);
		foreach ($event->getResults() as $eventResult)
		{
			$parameters = $eventResult->getParameters();
			$result[] = [
				'CREATE_DOM_LIST' => $parameters['CREATE_DOM_LIST'],
				'ADDITIONAL' => $parameters['ADDITIONAL'],
			];
		}

		return $result;
	}
}
