<?php
namespace Bitrix\Main\Numerator\Generator\Contract;
/**
 * Interface DynamicConfigurable -
 * for generators that can not use static values or user specified settings for template parse
 * generators receive values for parsing from outside on every run
 * from where they can get values for replace template words
 * @package Bitrix\Main\Numerator\Contract
 */
interface DynamicConfigurable
{
	/**
	 * @param array $config
	 */
	public function setDynamicConfig($config);
}