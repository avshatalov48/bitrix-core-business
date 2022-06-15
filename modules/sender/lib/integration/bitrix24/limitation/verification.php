<?php

namespace Bitrix\Sender\Integration\Bitrix24\Limitation;

use Bitrix\Main\Config\Option;

/**
 * @see \Bitrix\B24network\PhoneVerify
 * @see \Bitrix\Bitrix24\Controller\PhoneVerify
 * @see bitrix24/install/js/bitrix24/phoneverify/src/phoneverify.js
 */
class Verification
{
	public static function isEmailConfirmed(): bool
	{
		if (! \Bitrix\Main\Loader::includeModule('bitrix24'))
		{
			return true;
		}

		return \CBitrix24::isEmailConfirmed();
	}

	public static function isPhoneConfirmed(): bool
	{
		// no need to verify boxes
		if (! \Bitrix\Main\Loader::includeModule('bitrix24'))
		{
			return true;
		}

		// phone required only for new portals
		if (self::isMailingsUsed() && self::isForceCheckDisabled())
		{
			return true;
		}

		return \CBitrix24::isPhoneConfirmed();
	}

	/**
	 * Allow portals that already used mailings
	 */
	private static function isMailingsUsed(): bool
	{
		$letters = \Bitrix\Sender\Internals\Model\LetterTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=STATUS' => \Bitrix\Sender\Dispatch\Semantics::getFinishStates(),
				'=MESSAGE_CODE' => \Bitrix\Sender\Message\iBase::CODE_MAIL
			],
			'limit' => 1,
		]);

		return (bool)$letters->fetch();
	}

	/**
	 * For testing purposes
	 */
	private static function isForceCheckDisabled()
	{
		return Option::get('sender', 'force_phone_check', 'N') !== 'Y';
	}
}
