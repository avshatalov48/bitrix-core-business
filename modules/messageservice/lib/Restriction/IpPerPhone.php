<?php

namespace Bitrix\MessageService\Restriction;

use Bitrix\Main\Context;

class IpPerPhone extends Base
{
	use Parameterizable;


	public function getEntityId(): string
	{
		return 'ipp_' . $this->getEntity();
	}

	protected function getOptionLimitName(): string
	{
		return 'network_restriction_ip_per_phone';
	}

	protected function getEntity(): string
	{
		return $this->message->getTo();
	}

	protected function getCurrentAdditionalParam(): string
	{
		return Context::getCurrent()->getServer()->getRemoteAddr();
	}

	protected function getDefaultLimit(): int
	{
		return 0;
	}
}