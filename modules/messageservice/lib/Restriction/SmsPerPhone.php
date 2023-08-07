<?php

namespace Bitrix\MessageService\Restriction;

class SmsPerPhone extends Base
{

	public function getEntityId(): string
	{
		return 'spp_' . $this->getEntity();
	}

	protected function getOptionLimitName(): string
	{
		return 'network_restriction_sms_per_phone';
	}

	protected function getEntity(): string
	{
		return $this->message->getTo();
	}

	protected function getDefaultLimit(): int
	{
		return 6;
	}
}