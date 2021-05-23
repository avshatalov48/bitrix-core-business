<?php
namespace Bitrix\Im;

class Limit
{
	const COUNTER_CALL_SUCCESS = 'counter_call_success';
	const CALL_SCREEN_SHARING = 'call_screen_sharing';
	const CALL_RECORD = 'call_record';
	const CALL_BACKGROUND = 'call_background';
	const CALL_BLUR_BACKGROUND = 'call_blur_background';

	static $isActiveCallExtension = null;

	public static function getTypes()
	{
		$list = [];

		$list[] = self::getTypeCallScreenSharing();
		$list[] = self::getTypeCallRecord();
		$list[] = self::getTypeCallBackground();
		$list[] = self::getTypeCallBlurBackground();

		return $list;
	}

	public static function getTypesForJs()
	{
		$result = [];

		$list = self::getTypes();
		foreach ($list as $limit)
		{
			$result[] = [
				'id' => $limit['ID'],
				'articleCode' => $limit['ARTICLE_CODE'],
				'active' => $limit['ACTIVE'],
			];
		}

		return $result;
	}

	public static function getTypeCallScreenSharing()
	{
		return [
			'ID' => self::CALL_SCREEN_SHARING,
			'ACTIVE' => !self::isActiveCallExtension(),
			'ARTICLE_CODE' => 'limit_video_conference_screen_demonstration',
		];
	}

	public static function getTypeCallRecord()
	{
		return [
			'ID' => self::CALL_RECORD,
			'ACTIVE' => !self::isActiveCallExtension(),
			'ARTICLE_CODE' => 'limit_video_conference_record',
		];
	}

	public static function getTypeCallBackground()
	{
		return [
			'ID' => self::CALL_BACKGROUND,
			'ACTIVE' => !self::isActiveCallExtension(),
			'ARTICLE_CODE' => 'limit_video_own_background',
		];
	}

	public static function getTypeCallBlurBackground()
	{
		return [
			'ID' => self::CALL_BLUR_BACKGROUND,
			'ACTIVE' => !self::isActiveCallExtension(),
			'ARTICLE_CODE' => 'limit_video_blur_background',
		];
	}

	public static function isActiveCallExtension()
	{
		if (!is_null(static::$isActiveCallExtension))
		{
			return static::$isActiveCallExtension;
		}

		if (!\Bitrix\Main\Loader::includeModule('bitrix24'))
		{
			static::$isActiveCallExtension = true;
			return true;
		}

		$value = (int)\Bitrix\Bitrix24\Feature::getVariable('im_call_extensions_limit');
		if ($value === 0)
		{
			static::$isActiveCallExtension = true;
			return true;
		}

		$calls = self::getCounter(self::COUNTER_CALL_SUCCESS);
		if ($calls >= $value)
		{
			static::$isActiveCallExtension = false;
			return false;
		}

		static::$isActiveCallExtension = true;
		return true;
	}


	public static function getCounter(string $code)
	{
		$code = mb_strtolower($code);
		return \CGlobalCounter::GetValue('im_limit_'.$code, \CGlobalCounter::ALL_SITES);
	}

	public static function setCounter(string $code, int $value)
	{
		if ($code === self::COUNTER_CALL_SUCCESS)
		{
			static::$isActiveCallExtension = null;
		}

		$code = mb_strtolower($code);
		return \CGlobalCounter::Set('im_limit_'.$code, $value, \CGlobalCounter::ALL_SITES, '', false);
	}

	public static function incrementCounter(string $code)
	{
		if ($code === self::COUNTER_CALL_SUCCESS)
		{
			static::$isActiveCallExtension = null;
		}

		$code = mb_strtolower($code);
		return \CGlobalCounter::Increment('im_limit_'.$code, \CGlobalCounter::ALL_SITES, false);
	}
}