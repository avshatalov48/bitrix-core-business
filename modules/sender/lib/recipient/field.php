<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Recipient;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

/**
 * Class Field
 * @package Bitrix\Sender\Recipient
 */
class Field
{
	/**
	 * Get default.
	 *
	 * @return string
	 */
	public static function getDefaultName()
	{
		static $name;
		if (!$name)
		{
			$name = Option::get('sender', 'default_recipient_name', Loc::getMessage('SENDER_RECIPIENT_FIELD_DEFAULT_NAME'));
		}
		return $name;
	}
}