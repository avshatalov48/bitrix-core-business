<?php

namespace Bitrix\Sender\Consent;

interface iConsentMessageBuilder
{
	const EVENT_NAME = 'onSenderConsentBuilderList';
	
	/**
	 * set required fields for consent message
	 * @param array|null $fields
	 *
	 * @return mixed
	 */
	function setFields(?array $fields);

	/**
	 * build consent message
	 * @return mixed
	 */
	function buildMessage();
}