<?php

namespace Bitrix\Sender\Security;

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Internals\Model\AgreementTable;


/**
 * Class Agreement
 * @package Bitrix\Sender\Security
 **/
class Agreement
{
	/**
	 * Checks if User have access to module.
	 *
	 * @param integer $userId User ID.
	 * @return bool
	 */
	public static function isAcceptedByUser($userId)
	{
		$agreement = AgreementTable::getRow(array(
			'select' => array('ID'),
			'filter' => array('=USER_ID' => $userId),
			'limit' => 1,
			'cache' => array('ttl' => 3600)
		));

		return !empty($agreement);
	}

	/**
	 * Request agreement accept.
	 *
	 * @return void
	 */
	public static function requestFromCurrentUser()
	{
		if (User::current()->isAgreementAccepted())
		{
			return;
		}

		\CJSCore::init(array('sender_agreement'));
	}

	/**
	 * Return true if user accepted agreement.
	 *
	 * @return bool
	 */
	public static function acceptByCurrentUser()
	{
		if (User::current()->isAgreementAccepted())
		{
			return true;
		}

		$result = AgreementTable::add(array(
			'USER_ID' => User::current()->getId(),
			'NAME' => User::current()->getObject()->GetFullName(),
			'EMAIL' => User::current()->getObject()->GetEmail(),
			'IP_ADDRESS' => Context::getCurrent()->getRequest()->getRemoteAddress(),
		));

		return $result->isSuccess();
	}

	/**
	 * Get agreement text.
	 *
	 * @param bool $asRichHtml Get as rich html.
	 * @return string
	 */
	public static function getText($asRichHtml = false)
	{
		Loc::loadMessages(__FILE__);

		if ($asRichHtml)
		{
			$msg = Loc::getMessage("SENDER_SECURITY_AGREEMENT_HTML_RICH");
		}
		else
		{
			$msg = Loc::getMessage("SENDER_SECURITY_AGREEMENT_HTML_RICH");
		}

		return $msg;
	}

	/**
	 * Get agreement error text.
	 *
	 * @return string
	 */
	public static function getErrorText()
	{
		Loc::loadMessages(__FILE__);

		return Loc::getMessage("SENDER_SECURITY_AGREEMENT_ERROR");
	}
}