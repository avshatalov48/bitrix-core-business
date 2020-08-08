<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Security;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;


Loc::loadMessages(__FILE__);

/**
 * Class AccessChecker
 * @package Bitrix\Sender\Security
 */
class AccessChecker
{
	const ERR_CODE_VIEW = 'ERR_VIEW';
	const ERR_CODE_EDIT = 'ERR_EDIT';
	const ERR_CODE_NOT_FOUND = 'ERR_NOT_FOUND';

	/**
	 * Get message.
	 *
	 * @param string $code Code.
	 * @return string
	 */
	public static function getMessage($code)
	{
		$message = Loc::getMessage('SENDER_SECURITY_ACCESS_CHECKER_'.mb_strtoupper($code));
		return $message ?: 'Unknown error.';
	}

	/**
	 * Get error.
	 *
	 * @param string $code Code.
	 * @return Error
	 */
	public static function getError($code = self::ERR_CODE_VIEW)
	{
		return new Error(self::getMessage($code));
	}

	/**
	 * Add error.
	 *
	 * @param ErrorCollection $collection Error collection.
	 * @param string $code Code.
	 * @return void
	 */
	public static function addError(ErrorCollection $collection, $code = self::ERR_CODE_VIEW)
	{
		$collection->setError(self::getError($code));
	}

	/**
	 * Check view access.
	 *
	 * @param ErrorCollection $collection Error collection.
	 * @param User $user User.
	 * @return bool
	 */
	public static function checkViewAccess(ErrorCollection $collection, User $user = null)
	{
		$user = $user ?: User::current();
		if (!$user->canView())
		{
			$collection->setError(new Error(
				self::getMessage(self::ERR_CODE_VIEW),
				self::ERR_CODE_VIEW
			));
			return false;
		}

		return true;
	}
}