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
		if (defined('ADMIN_SECTION') && ADMIN_SECTION === true)
		{
			return 'messageservice_sender_sms.php?sender_id='.$this->getId();
		}

		return $this->isConfigurable() ? '/crm/configs/sms/?sender=' . $this->getId() : '';
	}
}