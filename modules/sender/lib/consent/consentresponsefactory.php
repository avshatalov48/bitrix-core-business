<?php

namespace Bitrix\Sender\Consent;

use Bitrix\Sender\Integration\EventHandler;
use Bitrix\Sender\Internals\CodeBasedFactory;

class ConsentResponseFactory extends CodeBasedFactory
{
	/**
	 * @param $code
	 *
	 * @return iConsentResponse|null
	 */
	public static function getConsentResponse($code)
	{
		return static::getObjectInstance(static::getInterface(), $code);
	}

	protected static function getInterface()
	{
		return iConsentResponse::class;
	}

	protected static function getClasses()
	{
		return [
			iConsentResponse::EVENT_NAME => EventHandler::onSenderConsentResponseList(),
		];
	}
}