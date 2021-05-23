<?php

namespace Bitrix\Rest\Configuration;

use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;

class Manifest
{
	public const ACCESS_TYPE_IMPORT = 'import';
	public const ACCESS_TYPE_EXPORT = 'export';
	public const ON_REST_APP_CONFIGURATION_GET_MANIFEST = 'OnRestApplicationConfigurationGetManifest';
	public const ON_REST_APP_CONFIGURATION_GET_MANIFEST_SETTING = 'OnRestApplicationConfigurationGetManifestSetting';
	private static $manifestList = [];

	public static function getList()
	{
		if (empty(static::$manifestList))
		{
			$event = new Event('rest', static::ON_REST_APP_CONFIGURATION_GET_MANIFEST);
			EventManager::getInstance()->send($event);
			foreach ($event->getResults() as $eventResult)
			{
				$manifestList = $eventResult->getParameters();
				if (is_array($manifestList))
				{
					static::$manifestList = array_merge(static::$manifestList, $manifestList);
				}
			}
		}

		return static::$manifestList;
	}

	public static function callEventInit($code, $params = [])
	{
		$result = [];
		$manifest = static::get($code);

		if ($manifest !== false && isset($params['TYPE']) && isset($params['CONTEXT_USER']))
		{
			$step = intval($params['STEP']);
			$setting = new Setting($params['CONTEXT_USER']);
			if ($step === 0)
			{
				$setting->delete(Setting::SETTING_MANIFEST);
			}

			$event = new Event(
				'rest',
				static::ON_REST_APP_CONFIGURATION_GET_MANIFEST_SETTING,
				[
					'CODE' => $manifest['CODE'],
					'MANIFEST' => $manifest,
					'TYPE' => $params['TYPE'],
					'CONTEXT' => $params['CONTEXT'] ? : false,
					'CONTEXT_USER' => $params['CONTEXT_USER'],
					'STEP' => $step,
					'NEXT' => isset($params['NEXT']) ? $params['NEXT'] : null,
					'ITEM_CODE' => $params['ITEM_CODE'] ? : null,
					'SETTING' => $setting->get(Setting::SETTING_MANIFEST)
				]
			);
			EventManager::getInstance()->send($event);
			foreach ($event->getResults() as $eventResult)
			{
				$parameters = $eventResult->getParameters();
				if (isset($parameters['SETTING']))
				{
					$setting->set(Setting::SETTING_MANIFEST, $parameters['SETTING']);
				}

				$result[] = [
					'NEXT' => isset($parameters['NEXT']) ? $parameters['NEXT'] : false,
					'ERROR_MESSAGES' => $parameters['ERROR_MESSAGES'],
					'ERROR_ACTION' => $parameters['ERROR_ACTION']
				];
			}
		}
		return $result;
	}

	public static function get($code)
	{
		$result = null;
		if ($code != '')
		{
			$manifestList = static::getList();
			$key = array_search($code, array_column($manifestList, 'CODE'));
			if ($key !== false)
			{
				$result = $manifestList[$key];
			}
		}

		return $result;
	}

	/**
	 * Check user access to action in manifest
	 *
	 * @param $type string static::ACCESS_TYPE_IMPORT | static::ACCESS_TYPE_EXPORT
	 * @param $manifestCode mixed
	 *
	 * @return array
	 */
	public static function checkAccess(string $type, $manifestCode = ''): array
	{
		$result = [
			'result' => false,
			'message' => '',
		];

		if (\CRestUtil::isAdmin())
		{
			$result['result'] = true;
		}
		elseif (!empty($manifestCode))
		{
			$manifest = static::get($manifestCode);
			try
			{
				if (
					!empty($manifest['ACCESS']['MODULE_ID'])
					&& is_array($manifest['ACCESS']['CALLBACK'])
					&& Loader::includeModule($manifest['ACCESS']['MODULE_ID'])
					&& is_callable($manifest['ACCESS']['CALLBACK'])
				)
				{
					$access = call_user_func($manifest['ACCESS']['CALLBACK'], $type, $manifest);
					$result['result'] = $access['result'] === true;
					$result['message'] = (is_string($access['message']) && $access['message'] !== '') ? $access['message'] : '';
				}
			}
			catch (\Exception $exception)
			{
			}
		}

		return $result;
	}
}