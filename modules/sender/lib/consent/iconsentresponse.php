<?php

namespace Bitrix\Sender\Consent;

interface iConsentResponse
{
	const EVENT_NAME = 'onSenderConsentResponseList';

	/**
	 * apply to send messages to recipient
	 * @return mixed
	 */
	public function apply();

	/**
	 * reject to send messages to recipient
	 * @return mixed
	 */
	public function reject();

	/**
	 * load data from request
	 * @param $data
	 *
	 * @return iConsentResponse
	 */
	public function loadData($data) : iConsentResponse;
}