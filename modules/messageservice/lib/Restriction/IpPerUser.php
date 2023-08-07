<?php

namespace Bitrix\MessageService\Restriction;

use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;

class IpPerUser extends Base
{
	use Parameterizable;
	use SkippingUnauthorized;

	public function getEntityId(): string
	{
		return 'ipu_' . $this->getEntity();
	}

	protected function getOptionLimitName(): string
	{
		return 'network_restriction_ip_per_user';
	}

	protected function getEntity(): string
	{
		return (string)($this->message->getAuthorId() ?: CurrentUser::get()->getId());
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