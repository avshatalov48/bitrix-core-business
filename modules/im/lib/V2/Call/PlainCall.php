<?php

namespace Bitrix\Im\V2\Call;

use Bitrix\Im\Call\Call;
use Bitrix\Im\Call\Util;
use Bitrix\Main\Config\Option;

class PlainCall extends Call
{
	protected $provider = parent::PROVIDER_PLAIN;

	protected function initCall(): void
	{
		if ($this->getState() == static::STATE_NEW)
		{
			if (empty($this->uuid))
			{
				$this->uuid = Util::generateUUID();
				$this->save();

				(new ControllerClient())->createCall($this);
			}
		}
	}

	public function finish(): void
	{
		if ($this->getState() != static::STATE_FINISHED)
		{
			(new ControllerClient())->finishCall($this);
		}
		parent::finish();
	}

	public function getMaxUsers(): int
	{
		return (int)Option::get('im', 'turn_server_max_users');
	}
}