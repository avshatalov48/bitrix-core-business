<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Main\UserConsent;

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

/**
 * Class Policy
 * @package Bitrix\Main\UserConsent
 */
class Policy
{
	/** @var array  */
	protected static $standardTextForLanguages = array('ru', 'ua');

	/** @var array  */
	protected static $requiredForLanguages = array('ru');

	/**
	 * Is consent required for language.
	 *
	 * @param string $languageId Language ID.
	 * @return bool
	 */
	public static function isRequired($languageId)
	{
		return in_array($languageId, self::$requiredForLanguages);
	}

	/**
	 * Return true if has standard consent text for language.
	 *
	 * @param string $languageId Language ID.
	 * @return string|null
	 */
	public static function hasText($languageId)
	{
		return in_array($languageId, self::$standardTextForLanguages);
	}

	/**
	 * Install default.
	 *
	 * @return void
	 */
	public static function installDefault()
	{
		$languageId = Context::getCurrent()->getLanguage();
		if (!self::isRequired($languageId) || !self::hasText($languageId))
		{
			return;
		}


	}
}