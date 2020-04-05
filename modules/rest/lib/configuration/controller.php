<?php

namespace Bitrix\Rest\Configuration;

use Bitrix\Main\EventManager;
use Bitrix\Main\Event;

class Controller
{
	const ON_REST_APP_CONFIGURATION_CLEAR = 'OnRestApplicationConfigurationClear';
	const ON_REST_APP_CONFIGURATION_ENTITY = 'OnRestApplicationConfigurationEntity';
	const ON_REST_APP_CONFIGURATION_EXPORT = 'OnRestApplicationConfigurationExport';
	const ON_REST_APP_CONFIGURATION_IMPORT = 'OnRestApplicationConfigurationImport';

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
			if(is_array($codeList))
			{
				$result = array_merge($result, $codeList);
			}
		}
		asort($result);

		return array_keys($result);
	}

	public static function callEventExport($manifestCode, $code, $step = 0, $next = '', $itemCode = '')
	{
		$result = [];
		if($manifestCode == '')
		{
			return $result;
		}

		$manifest = Manifest::get($manifestCode);
		if(!is_null($manifest))
		{
			$event = new Event(
				'rest',
				static::ON_REST_APP_CONFIGURATION_EXPORT,
				[
					'CODE' => $code,
					'STEP' => $step,
					'NEXT' => $next,
					'MANIFEST' => $manifest,
					'ITEM_CODE' => $itemCode
				]
			);
			EventManager::getInstance()->send($event);
			foreach ($event->getResults() as $eventResult)
			{
				$parameters = $eventResult->getParameters();
				$result[] = [
					'FILE_NAME' => $parameters['FILE_NAME'],
					'CONTENT' => $parameters['CONTENT'],
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
			'FINISH' => true,
			'NEXT' => 0
		];
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
			];
		}

		return $result;
	}

	public static function callEventImport($code, $content, $ratio, $context = 'external')
	{
		$result = [];
		$event = new Event(
			'rest',
			static::ON_REST_APP_CONFIGURATION_IMPORT,
			[
				'CODE' => $code,
				'CONTENT' => $content,
				'RATIO' => $ratio,
				'CONTEXT' => $context
			]
		);
		EventManager::getInstance()->send($event);
		foreach ($event->getResults() as $eventResult)
		{
			$parameters = $eventResult->getParameters();
			$result[] = [
				'RATIO' => $parameters['RATIO'],
				'ERROR_MESSAGES' => $parameters['ERROR_MESSAGES'],
				'ERROR_ACTION' => $parameters['ERROR_ACTION']
			];
		}

		return $result;
	}

}