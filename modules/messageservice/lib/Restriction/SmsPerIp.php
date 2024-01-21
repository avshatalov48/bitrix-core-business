<?php

namespace Bitrix\MessageService\Restriction;

use Bitrix\Main\Context;

class SmsPerIp extends Base
{
	public function getEntityId(): string
	{
		return 'spip_' . $this->getEntity();
	}

	protected function getOptionLimitName(): string
	{
		return 'network_restriction_sms_per_ip';
	}

	protected function getEntity(): string
	{
		return (string)Context::getCurrent()->getServer()->getRemoteAddr();
	}

	protected function getDefaultLimit(): int
	{
		return 0;
	}
}
