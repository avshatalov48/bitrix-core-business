<?php
namespace Bitrix\Main\Numerator\Generator\Contract;
use Bitrix\Main\Result;

/**
 * Interface UserConfigurable -
 * for generators that have configurations fields that can be set by users
 * generators store their settings values in db
 * @package Bitrix\Main\Numerator\Contract
 */
interface UserConfigurable
{
	/**
	 * @param array|null $config
	 */
	public function setConfig($config);
	/**
	 * @return array of configuration fields and their values
	 */
	public function getConfig();
	/**
	 * @param array $config
	 * @return Result
	 */
	public function validateConfig($config);
	/**
	 * @return array of configuration fields that can be edited by user
	 */
	public static function getSettingsFields();
}
