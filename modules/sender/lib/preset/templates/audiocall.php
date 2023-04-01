<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Preset\Templates;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Sender\Message;
use Bitrix\Sender\Templates\Type;

Loc::loadMessages(__FILE__);

/**
 * Class Sms
 * @package Bitrix\Sender\Preset\Templates
 */
class AudioCall
{
	const AUDIO_DIR = 'http://dl.bitrix24.com/sender/audiocall/';
	const METADATA_FILE = 'http://dl.bitrix24.com/sender/audiocall/metadata.json';

	/**
	 * Get supported lang codes
	 * @return array
	 */
	public static function getSupportedLangs()
	{
		$data = self::getMetadata();
		return $data ? $data['langs'] : [];
	}

	/**
	 * Return base templates.
	 *
	 * @param string|null $templateType Template type.
	 * @param string|null $templateId Template ID.
	 * @param string|null $messageCode Message code.
	 * @return array
	 */
	public static function onPresetTemplateList($templateType = null, $templateId = null, $messageCode = null)
	{
		if ($templateType && $templateType !== 'BASE')
		{
			return array();
		}
		if ($messageCode && $messageCode !== Message\iBase::CODE_AUDIO_CALL)
		{
			return array();
		}

		return static::getTemplates($templateId);
	}

	/**
	 * Get audio file url by preset code
	 * @param string $code Preset code.
	 * @return bool|string
	 */
	public static function getAudioFileUrlByCode($code)
	{
		if (!self::presetExists($code))
		{
			return false;
		}
		$filePath = static::AUDIO_DIR . static::getCodeWithLang($code) . '.mp3';
		return $filePath;
	}

	/**
	 * Does preset exists
	 * @param string $code Preset code.
	 * @return bool
	 */
	public static function presetExists($code)
	{
		return !!self::getDurationByCode($code);
	}

	/**
	 * Get audio duration by preset code
	 * @param string $code Preset code.
	 * @return bool|int
	 */
	public static function getDurationByCode($code)
	{
		$code = self::getCodeWithLang($code);
		$data = self::getMetadata();
		return $data['durations'][$code] ?? false;
	}

	/**
	 * Get default preset code
	 * @return mixed
	 */
	public static function getDefaultCode()
	{
		$messageCode =  Message\iBase::CODE_AUDIO_CALL;
		foreach (Texts::getListByType($messageCode) as $item)
		{
			$code = mb_strtolower($item['CODE']);
			if (self::presetExists($code))
			{
				return $code;
			}
		}
		return false;
	}

	/**
	 * Get preset code with lang
	 * @param string $code Preset code.
	 * @return string
	 */
	private static function getCodeWithLang($code)
	{
		$lang = self::getLang();
		return $lang . '_' . $code;
	}

	/**
	 * Get current audio lang
	 * @return string
	 */
	private static function getLang()
	{
		$lang = mb_strtolower(LANGUAGE_ID);
		$supportedLangs = static::getSupportedLangs();
		$lang = in_array($lang, $supportedLangs) ? $lang : array_shift($supportedLangs);
		return $lang;
	}

	/**
	 * Get templates list
	 * @param null|int $templateId Template id.
	 * @return array
	 */
	private static function getTemplates($templateId = null)
	{
		$result = [];
		$messageCode =  Message\iBase::CODE_AUDIO_CALL;

		foreach (Texts::getListByType($messageCode) as $item)
		{
			$code = mb_strtolower($item['CODE']);
			$presetCode = mb_strtolower($messageCode."_".$code);
			if (!self::presetExists($code))
			{
				continue;
			}
			if($templateId && $presetCode !== $templateId)
			{
				continue;
			}

			$result[] = array(
				'ID' => $presetCode,
				'TYPE' => Type::getCode(Type::BASE),
				'MESSAGE_CODE' => array($messageCode),
				'VERSION' => 2,
				'HOT' => $item['HOT'],
				'ICON' => $item['ICON'],

				'NAME' => $item['NAME'],
				'DESC' => $item['DESC'],
				'FIELDS' => array(
					'AUDIO_FILE' => [
						'CODE' => 'AUDIO_FILE',
						'VALUE' => static::getAudioFileUrlByCode($code),
					]
				),
			);
		}

		return $result;
	}

	/**
	 * Get presets extra data
	 * @return array|bool
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function getMetadata()
	{
		static $failed = false;
		if ($failed)
		{
			return false;
		}

		$cacheTtl = 86400; // 24 hours
		$cacheId = 'sender_audiocall_metadata';
		$cachePath = '/sender/audiocall_metadata/';
		$cache = \Bitrix\Main\Application::getInstance()->getCache();
		if($cache->initCache($cacheTtl, $cacheId, $cachePath))
		{
			return $cache->getVars();
		}
		else
		{
			$result = false;

			$cache->startDataCache();

			$request = new HttpClient([
				"socketTimeout" => 5,
				"streamTimeout" => 5
			]);
			$request->get(static::METADATA_FILE);
			if($request->getStatus() == 200)
			{
				try
				{
					$result = Json::decode($request->getResult());
				}
				catch (ArgumentException $e)
				{
				}
			}
			if (is_array($result))
			{
				$cache->endDataCache($result);
			}
			else
			{
				$failed = true;
				$cache->abortDataCache();
			}
			return $result;
		}
	}
}