<?php

namespace Bitrix\Rest\Configuration;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Web\Json;

/**
 * Temp work with current context for step by step action
 */
class Setting
{
	const SETTING_MANIFEST = 'SETTING_MANIFEST';
	const SETTING_RATIO = 'SETTING_RATIO';
	const SETTING_APP_INFO = 'APP_INFO';
	const SETTING_EXPORT_ARCHIVE_NAME = 'EXPORT_ARCHIVE_NAME';

	private $context = 'null';
	private $optionCode = '~configuration_action_setting';
	private $optionModule = 'rest';
	private $ttlContext = 14400;//3600*4

	/**
	 * @param $context string [a-zA-Z0-9_]
	 */
	public function __construct($context)
	{
		if ($context != '')
		{
			$context = preg_replace('/[^a-zA-Z0-9_]/', '', $context);
			$this->context = $context;
		}
	}

	/**
	 * Add data in context
	 *
	 * @param $code string needed code setting
	 * @param $data mixed any saved data
	 *
	 * @return boolean
	 */
	public function set($code, $data)
	{
		$option = $this->getOption();
		$option['TTL_CONTENT'][$this->context] = time();
		$option['CONTENT'][$this->context][$code] = $data;
		return $this->saveOption($option);
	}

	/**
	 * Return needed setting by code with context
	 * @param $code string
	 *
	 * @return mixed|null
	 */
	public function get($code)
	{
		$settingList = $this->getFull();
		return array_key_exists($code, $settingList) ? $settingList[$code] : null;
	}

	/**
	 * All setting with context
	 *
	 * @return array
	 */
	public function getFull()
	{
		$return = [];
		$data = $this->getOption();
		if (!empty($data['CONTENT'][$this->context]))
		{
			if ($data['TTL_CONTENT'][$this->context] < (time() + $this->ttlContext))
			{
				$return = $data['CONTENT'][$this->context];
			}
			else
			{
				unset($data['CONTENT'][$this->context], $data['TTL_CONTENT'][$this->context]);
				$this->saveOption($data);
			}
		}

		return $return;
	}

	/**
	 * @param $code string
	 *
	 * @return boolean
	 */
	public function delete($code)
	{
		$option = $this->getOption();
		if (isset($option['CONTENT'][$this->context]) && array_key_exists($code, $option['CONTENT'][$this->context]))
		{
			unset($option['CONTENT'][$this->context][$code]);

			return $this->saveOption($option);
		}

		return false;
	}

	/**
	 * @return boolean
	 */
	public function deleteFull()
	{
		$option = $this->getOption();
		if (isset($option['CONTENT'][$this->context]))
		{
			unset($option['CONTENT'][$this->context]);
			return $this->saveOption($option);
		}

		return true;
	}

	/**
	 * All setting
	 *
	 * @return array
	 */
	private function getOption()
	{
		$data = Option::get($this->optionModule, $this->optionCode);
		if ($data)
		{
			try
			{
				$data = Json::decode($data);
			}
			catch (ArgumentException $e)
			{
				$data = [];
			}
		}
		else
		{
			$data = [];
		}

		return $data;
	}

	private function saveOption($data)
	{
		if (is_array($data))
		{
			Option::set($this->optionModule, $this->optionCode, Json::encode($data));

			return true;
		}

		return false;
	}

	public function deleteOption()
	{
		Option::delete($this->optionModule, ['name' => $this->optionCode]);

		return true;
	}
}