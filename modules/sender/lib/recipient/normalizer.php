<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Recipient;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\PhoneNumber;

Loc::loadMessages(__FILE__);

/**
 * Class Normalizer
 * @package Bitrix\Sender\Recipient
 */
class Normalizer
{
	/**
	 * Normalize.
	 *
	 * @param string $code Code.
	 * @param integer $typeId Type ID.
	 * @return string|null
	 */
	public static function normalize($code, $typeId = Type::EMAIL)
	{
		if (!$code)
		{
			return null;

		}
		switch ($typeId)
		{
			case Type::IM:
				return self::normalizeIm($code);

			case Type::PHONE:
				return self::normalizePhone($code);

			case Type::CRM_COMPANY_ID:
			case Type::CRM_CONTACT_ID:
				return self::normalizeCrmEntityId($code);

			case Type::EMAIL:
			default:
				return self::normalizeEmail($code);
		}
	}

	/**
	 * Normalize email.
	 *
	 * @param string $code Code.
	 * @return string
	 */
	public static function normalizeEmail($code)
	{
		return trim(strtolower($code));
	}

	/**
	 * Normalize phone number.
	 *
	 * @param string $phone Phone number.
	 * @return string|null
	 */
	public static function normalizePhone($phone)
	{
		return PhoneNumber\Parser::getInstance()
			->parse($phone)
			->format(PhoneNumber\Format::E164);
	}

	/**
	 * Normalize im.
	 *
	 * @param string $code Code.
	 * @return string
	 */
	public static function normalizeIm($code)
	{
		$code = trim($code);
		if (strpos($code, 'imol|') === 0)
		{
			$code = substr($code, 5);
		}

		return $code;
	}

	/**
	 * Normalize Crm entity code.
	 *
	 * @param string $code Code.
	 * @return string
	 */
	public static function normalizeCrmEntityId($code)
	{
		return preg_replace("/[^0-9]/", '', $code);
	}
}