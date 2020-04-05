<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Recipient;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class Validator
 * @package Bitrix\Sender\Recipient
 */
class Validator
{
	/**
	 * Validate.
	 *
	 * @param string $code Code.
	 * @param integer $typeId Type ID.
	 * @return string
	 */
	public static function validate($code, $typeId = Type::EMAIL)
	{
		switch ($typeId)
		{
			case Type::IM:
				return self::validateIm($code);

			case Type::PHONE:
				return self::validatePhone($code);

			case Type::CRM_CONTACT_ID:
			case Type::CRM_COMPANY_ID:
				return self::validateCrmEntityId($code);

			case Type::EMAIL:
			default:
				return self::validateEmail($code);
		}
	}

	/**
	 * Validate email.
	 *
	 * @param string $email Email.
	 * @return string
	 */
	public static function validateEmail($email)
	{
		return check_email($email);
	}

	/**
	 * Validate phone number.
	 *
	 * @param string $phone Phone number.
	 * @return bool
	 */
	public static function validatePhone($phone)
	{
		return (bool) preg_match('/^[\+]?[\d]{4,25}$/', $phone);
	}

	/**
	 * Validate im code.
	 *
	 * @param string $code Code.
	 * @return bool
	 */
	public static function validateIm($code)
	{
		return (bool) preg_match('/^[\d]+\|[\d]+$/', $code);
	}

	/**
	 * Validate Crm entity code.
	 *
	 * @param string $code Code.
	 * @return bool
	 */
	public static function validateCrmEntityId($code)
	{
		return (bool) preg_match('/^[\d]$/', $code);
	}
}