<?php

namespace Bitrix\Sender\Transport;

use Bitrix\Sender\Consent\AbstractConsentMessageBuilder;
use Bitrix\Sender\Message;

interface iConsent
{
	/**
	 * send consent message to recipient
	 * @param AbstractConsentMessageBuilder $builder
	 *
	 * @return mixed|bool
	 */
	public function sendConsent(Message\Adapter $message, AbstractConsentMessageBuilder $builder);

	/**
	 * check if consent need
	 * @return boolean
	 */
	public function isConsentNeed();

	/**
	 * return max consent request num
	 * @return int
	 */
	public function getConsentMaxRequests() : int;
}