<?php

namespace Bitrix\MessageService\Restriction;

use Bitrix\Main\Engine\CurrentUser;

class UserPerPhone extends Base
{
	use Parameterizable;

	public function getEntityId(): string
	{
		return 'upp_' . $this->getEntity();
	}

	public function isCanSend(): bool
	{
		if ($this->getCurrentAdditionalParam() === '0')
		{
			return true;
		}

		return parent::isCanSend();
	}

	public function increase(): bool
	{
		if ($this->getCurrentAdditionalParam() === '0')
		{
			return true;
		}

		return parent::increase();
	}


	protected function getOptionLimitName(): string
	{
		return 'network_restriction_user_per_phone';
	}

	protected function getEntity(): string
	{
		return $this->message->getTo();
	}

	protected function getCurrentAdditionalParam(): string
	{
		return (string)($this->message->getAuthorId() ?: CurrentUser::get()->getId());
	}

	protected function getDefaultLimit(): int
	{
		return 0;
	}
}