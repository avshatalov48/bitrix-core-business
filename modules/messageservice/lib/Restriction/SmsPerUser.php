<?php

namespace Bitrix\MessageService\Restriction;

use Bitrix\Main\Engine\CurrentUser;

class SmsPerUser extends Base
{
	use SkippingUnauthorized;

	public function getEntityId(): string
	{
		return 'spu_' . $this->getEntity();
	}

	protected function getOptionLimitName(): string
	{
		return 'network_restriction_sms_per_user';
	}

	protected function getEntity(): string
	{
		return (string)($this->message->getAuthorId() ?: CurrentUser::get()->getId());
	}

	protected function getDefaultLimit(): int
	{
		return 4;
	}
}