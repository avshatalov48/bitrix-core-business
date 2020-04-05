<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Recipient;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Internals\ClassConstant;

Loc::loadMessages(__FILE__);

/**
 * Class Type
 * @package Bitrix\Sender\Recipient
 */
class Type extends ClassConstant
{
	const EMAIL = 1;
	const PHONE = 2;
	const IM = 3;
	const CRM_COMPANY_ID = 4;
	const CRM_CONTACT_ID = 5;

	/**
	 * Detect type by recipient code.
	 *
	 * @param string $recipientCode Recipient code.
	 * @param bool $isNormalized Is code normalized.
	 * @return integer|null
	 */
	public static function detect($recipientCode, $isNormalized = false)
	{
		$list = self::getNamedList();
		unset($list[self::PHONE]);
		$list = array_keys($list);
		$list[] = self::PHONE;

		foreach ($list as $id)
		{
			if ($isNormalized)
			{
				$normalizedCode = $recipientCode;
			}
			else
			{
				$normalizedCode = Normalizer::normalize($recipientCode, $id);
			}

			if (!Validator::validate($normalizedCode, $id))
			{
				continue;
			}

			return $id;
		}

		return null;
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
		$name = Loc::getMessage('SENDER_TYPE_CAPTION_' . $code) ?: $code;
		return $name;
	}
}