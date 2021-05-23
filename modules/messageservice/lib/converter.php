<?php
namespace Bitrix\MessageService;

use Bitrix\Main;
use Bitrix\Bizproc;

class Converter
{
	public static function onInstallModule()
	{
		static::convertBizprocProviders();
		static::convertOptions();
	}

	public static function convertBizprocProviders()
	{
		if (!Main\Loader::includeModule('bizproc'))
			return false;

		$providerList = Bizproc\RestProviderTable::getList();

		while ($row = $providerList->fetch())
		{
			static::addRestSender(array(
				'APP_ID' => $row['APP_ID'],
				'APP_NAME' => $row['APP_NAME'],
				'CODE' => $row['CODE'],
				'TYPE' => $row['TYPE'],
				'HANDLER' => $row['HANDLER'],
				'NAME' => $row['NAME'],
				'DESCRIPTION' => $row['DESCRIPTION']
			));
		}
		return true;
	}

	public static function convertOptions()
	{
		$checkList = array('smsru', 'twilio');

		foreach ($checkList as $senderId)
		{
			$optionString = Main\Config\Option::get('crm', 'integration.sms.'.$senderId);
			if (!$optionString)
				continue;

			$options = unserialize($optionString, ['allowed_classes' => false]);
			if (!is_array($options))
				continue;

			if (isset($options['default_sender']))
			{
				$options['default_from'] = $options['default_sender'];
				unset($options['default_sender']);
			}

			Main\Config\Option::set('messageservice','sender.sms.'.$senderId, serialize($options));
		}
	}

	private static function addRestSender($params)
	{
		$iterator = Internal\Entity\RestAppTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=APP_ID' => $params['APP_ID'],
				'=CODE' => $params['CODE']
			)
		));
		$result = $iterator->fetch();
		if ($result)
		{
			return true;
		}

		$senderLang = array(
			'NAME' => $params['NAME'],
			'DESCRIPTION' => $params['DESCRIPTION'],
			'APP_NAME' => $params['APP_NAME']
		);
		unset($params['NAME'], $params['DESCRIPTION'], $params['APP_NAME']);

		$params['AUTHOR_ID'] = 0;
		$result = Internal\Entity\RestAppTable::add($params);

		if ($result->getErrors())
		{
			return false;
		}

		$senderLang['APP_ID'] = $result->getId();
		static::addRestSenderLang($senderLang);

		return true;
	}

	private static function addRestSenderLang($langFields)
	{
		$langData = array();

		foreach ($langFields['NAME'] as $langId => $langName)
		{
			$langCode = mb_strtolower($langId);
			if ($langCode === '*')
				$langCode = '**';

			$langData[$langCode] = array(
				'APP_ID' => $langFields['APP_ID'],
				'LANGUAGE_ID' => $langCode,
				'NAME' => $langFields['NAME'][$langId],
				'DESCRIPTION' => isset($langFields['DESCRIPTION'][$langId])
					? (string)$langFields['DESCRIPTION'][$langId] : null,
				'APP_NAME' => isset($langFields['APP_NAME'][$langId])
					? (string)$langFields['APP_NAME'][$langId] : null,
			);

			if (!isset($langData['**']))
			{
				$langData['**'] = $langData[$langCode];
				$langData['**']['LANGUAGE_ID'] = '**';
			}
		}

		foreach ($langData as $toAdd)
		{
			Internal\Entity\RestAppLangTable::add($toAdd);
		}
	}
}