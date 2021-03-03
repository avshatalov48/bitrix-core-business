<?php

namespace Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields;

interface IField
{
	/**
	 * get property default value
	 * @return mixed
	 */
	static function getDefaultValue();

	/**
	 * check property value
	 * @param $value
	 *
	 * @return bool
	 */
	static function checkValue($value) : bool;


	/**
	 *  check if field is available
	 * @return bool
	 */
	static function available() : bool;

	/**
	 * check if field is required
	 * @return bool
	 */
	static function required() : bool;
}