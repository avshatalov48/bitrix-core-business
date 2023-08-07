<?php

namespace Bitrix\MessageService\Restriction;

class PhonePerUser extends Base
{
	use Parameterizable;
	use SkippingUnauthorized;

	public function getEntityId(): string
	{
		return 'ppu_' . $this->getEntity();
	}

	protected function getOptionLimitName(): string
	{
		return 'network_restriction_phone_per_user';
	}

	protected function getEntity(): string
	{
		global $USER;
		$userId = is_object($USER) ? $USER->getId() : 0;

		return (string)($this->message->getAuthorId() ?: $userId);
	}

	protected function getCurrentAdditionalParam(): string
	{
		return $this->message->getTo();
	}

	protected function getDefaultLimit(): int
	{
		return 2;
	}
}