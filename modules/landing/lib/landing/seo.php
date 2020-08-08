<?php
namespace Bitrix\Landing\Landing;

use \Bitrix\Landing\Manager;

class Seo
{
	/**
	 * Stored seo keys.
	 * @var array
	 */
	private static $storedKeys = [
		'title' => null,
		'description' => null,
		'keywords' => null
	];

	/**
	 * Changed seo keys.
	 * @var array
	 */
	private static $changedKeys = [
		'title' => null,
		'description' => null,
		'keywords' => null
	];

	/**
	 * Keep seo keys for analyze in future.
	 * @return void
	 */
	public static function beforeLandingView()
	{
		$application = Manager::getApplication();

		foreach (self::$storedKeys as $key => $val)
		{
			$currentVal = $application->getProperty($key);
			if (is_string($currentVal))
			{
				self::$storedKeys[$key] = htmlspecialcharsback($currentVal);
			}
		}
	}

	/**
	 * Analyze stored seo keys.
	 * @return void
	 */
	public static function afterLandingView()
	{
		$application = Manager::getApplication();

		foreach (self::$storedKeys as $key => $val)
		{
			$newVal = $application->getProperty($key);
			if (is_string($newVal) && $newVal != $val)
			{
				self::$changedKeys[$key] = htmlspecialcharsback($newVal);
			}
		}
	}

	/**
	 * If seo key was changed then returns changed value, else returns $value.
	 * @param string $key Seo key.
	 * @param string $value Seo value.
	 * @return string
	 */
	public static function processValue($key, $value)
	{
		if (
			is_string($key) &&
			isset(self::$changedKeys[$key])
		)
		{
			return trim(self::$changedKeys[$key]);
		}

		return trim($value);
	}

	/**
	 * Manually changes some seo key.
	 * @param string $key Seo key.
	 * @param string $value Seo value.
	 * @return void
	 */
	public static function changeValue($key, $value)
	{
		if (
			is_string($key) &&
			is_string($value) &&
			array_key_exists($key, self::$changedKeys)
		)
		{
			self::$changedKeys[$key] = $value;
		}
	}
}