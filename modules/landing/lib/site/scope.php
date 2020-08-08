<?php
namespace Bitrix\Landing\Site;

abstract class Scope
{
	protected static $currentScopeId = null;

	/**
	 * Method for first time initialization scope.
	 * @param array $params Additional params.
	 * @return void
	 */
	public static function init(array $params = [])
	{
		$reflectionClass = new \ReflectionClass(get_called_class());
		self::$currentScopeId = mb_strtoupper($reflectionClass->getShortName());
	}

	/**
	 * Returns current scope id.
	 * @return string|null
	 */
	public static function getCurrentScopeId()
	{
		return self::$currentScopeId;
	}

	/**
	 * Should return publication path string.
	 * @return string
	 */
	abstract public static function getPublicationPath();

	/**
	 * Should return general key for site path (ID or CODE).
	 * @return string
	 */
	abstract public static function getKeyCode();

	/**
	 * Should return domain id for new site.
	 * @return int|string
	 */
	abstract public static function getDomainId();

	/**
	 * Should return filter value for 'TYPE' key.
	 * @return mixed
	 */
	abstract public static function getFilterType();

	/**
	 * Returns array of hook's codes, which excluded by scope.
	 * @return array
	 */
	abstract public static function getExcludedHooks(): array;
}