<?php

namespace Bitrix\Seo\BusinessSuite\Configuration;

use JsonSerializable;
use Bitrix\Seo\BusinessSuite\AuthAdapter\IAuthSettings;

interface IConfig extends IAuthSettings, JsonSerializable
{
	/**
	 * @param array $array
	 *
	 * @return static
	 */
	public static function loadFromArray(array $array) : self;
	/**
	 * load current configuration
	 * @return static|null
	 */
	public static function load() : ?self;

	/**
	 * get default configuration
	 * @return static
	 */
	public static function default() : self;

	/**
	 * @param string $name
	 * @param $value
	 *
	 * @return $this
	 */
	public function set(string $name,$value) : self;

	/**
	 * @param string $name
	 *
	 * @return mixed
	 */

	public function get(string $name);

	/**
	 * save changes
	 * @return bool
	 */
	public function save() : bool;
}