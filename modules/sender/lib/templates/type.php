<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Templates;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Internals\ClassConstant;

Loc::loadMessages(__FILE__);

/**
 * Class Type
 * @package Bitrix\Sender\Templates
 */
class Type extends ClassConstant
{
	const BASE = 1;
	const USER = 2;
	const ADDITIONAL = 3;
	const SITE_TMPL = 4;

	/**
	 * Get caption.
	 *
	 * @param integer $id ID.
	 * @return integer|null
	 */
	public static function getName($id)
	{
		$name = Loc::getMessage('SENDER_TEMPLATES_TYPE_CAPTION_' . self::getCode($id));
		return $name ?: parent::getName($id);
	}
}