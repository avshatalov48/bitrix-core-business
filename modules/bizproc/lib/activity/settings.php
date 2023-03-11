<?php

namespace Bitrix\Bizproc\Activity;

use Bitrix\Main\Text\Emoji;

class Settings
{
	private string $category;

	public function __construct(string $category)
	{
		$this->category = $category;
	}

	/**
	 * @return array | string | null
	 */
	public function get()
	{
		$settings = \CUserOptions::GetOption($this->category, 'activity_settings');
		if (is_array($settings) || is_string($settings))
		{
			static::decodeSettings($settings);
		}
		else
		{
			$settings = null;
		}

		return $settings;
	}

	/**
	 * @param array | string $settings
	 * @return void
	 */
	public function save($settings): void
	{
		static::encodeSettings($settings);
		\CUserOptions::SetOption($this->category, 'activity_settings', $settings);
	}

	/**
	 * @param array | string &$settings
	 * @return void
	 */
	public static function decodeSettings(&$settings): void
	{
		if (is_array($settings))
		{
			array_walk_recursive(
				$settings,
				function (&$value) {
					if (is_string($value))
					{
						$value = Emoji::decode($value);
					}
				}
			);
		}
		elseif (is_string($settings))
		{
			Emoji::decode($settings);
		}
	}

	/**
	 * @param array | string &$settings
	 * @return void
	 */
	public static function encodeSettings(&$settings): void
	{
		if (is_array($settings))
		{
			array_walk_recursive(
				$settings,
				function (&$value) {
					if (is_string($value))
					{
						$value = Emoji::encode($value);
					}
				}
			);
		}
		elseif (is_string($settings))
		{
			$settings = Emoji::encode($settings);
		}
	}
}