<?php

namespace Bitrix\MessageService\Providers\Base;

use Bitrix\MessageService\MessageType;
use Bitrix\MessageService\Providers;

abstract class Informant implements Providers\Informant
{
	public function getType(): string
	{
		return MessageType::SMS;
	}

	public function getExternalId(): string
	{
		return $this->getType() . ':' . $this->getId();
	}

	public function getManageUrl(): string
	{
		return $this->isConfigurable() ? '/crm/configs/sms/?sender=' . $this->getId() : '';
	}
}