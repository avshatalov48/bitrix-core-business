<?php

namespace Bitrix\MessageService\Restriction;

trait SkippingUnauthorized
{
	public function isCanSend(): bool
	{
		if ($this->getEntity() === '0')
		{
			return true;
		}
		return parent::isCanSend();
	}

	public function increase(): bool
	{
		if ($this->getEntity() === '0')
		{
			return true;
		}

		return parent::increase();
	}
}