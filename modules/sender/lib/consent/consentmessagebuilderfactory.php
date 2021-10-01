<?php

namespace Bitrix\Sender\Consent;

use Bitrix\Sender\Integration\EventHandler;
use Bitrix\Sender\Internals\CodeBasedFactory;

class ConsentMessageBuilderFactory extends CodeBasedFactory
{
	const TEST_POSTFIX = '_test';

	/**
	 * Get consent message builder instance by code.
	 *
	 * @param string $code code.
	 *
	 * @return null|iConsentMessageBuilder
	 */
	public static function getConsentBuilder(string $code): ?iConsentMessageBuilder
	{
		return static::getObjectInstance(static::getInterface(), $code);
	}

	public static function getTestMessageConsentBuilder($code)
	{
		return static::getObjectInstance(static::getInterface(), $code . static::TEST_POSTFIX);
	}

	protected static function getInterface(): string
	{
		return iConsentMessageBuilder::class;
	}

	protected static function getClasses(): array
	{
		return [iConsentMessageBuilder::EVENT_NAME => EventHandler::onSenderConsentMessageBuildersList()];
	}
}