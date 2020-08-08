<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Recipient;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;
use Bitrix\Sender\Internals\ClassConstant;

Loc::loadMessages(__FILE__);

/**
 * Class Agent
 * @package Bitrix\Sender\Recipient
 */
class Agent extends ClassConstant
{
	const UNDEFINED = 0;
	const GMAIL = 1;
	const IPHONE = 2;
	const IPAD = 3;
	const ANDROID = 4;
	const OUTLOOK = 5;
	const THUNDERBIRD = 6;
	const YANDEX = 7;

	/**
	 * Detect agent by user agent string.
	 *
	 * @param string|null $string String.
	 * @return integer|null
	 */
	public static function detect($string = null)
	{
		if (!$string)
		{
			$string = Context::getCurrent()->getRequest()->getUserAgent();
		}

		$string = mb_strtolower($string);
		$rules = self::getRules();
		foreach ($rules as $id => $searchList)
		{
			foreach ($searchList as $search)
			{
				if (mb_strpos($string, $search) === false)
				{
					continue;
				}

				return $id;
			}
		}

		return self::UNDEFINED;
	}

	/**
	 * Get caption.
	 *
	 * @param integer $id ID.
	 * @return integer|null
	 */
	public static function getName($id)
	{
		$code = self::getCode($id);
		$name = Loc::getMessage('SENDER_AGENT_CAPTION_' . $code) ?: $code;
		return $name;
	}

	/**
	 * Get rules.
	 *
	 * @return array
	 */
	protected static function getRules()
	{
		$rules = array(
			self::GMAIL => array('googleimageproxy'),
			self::IPHONE => array('iphone'),
			self::IPAD => array('ipad'),
			self::ANDROID => array('android'),
			self::OUTLOOK => array('outlook'),
			self::THUNDERBIRD => array('thunderbird'),
			self::YANDEX => array('yandex'),
		);

		return $rules;
	}
}