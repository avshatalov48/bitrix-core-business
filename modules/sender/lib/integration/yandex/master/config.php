<?php

namespace Bitrix\Sender\Integration\Yandex\Master;

use Bitrix\Main\Config\Configuration;

class Config
{
	public function getPartnerId(): ?int
	{
		return Configuration::getInstance('sender')->get('yandex.integration.widget')['partnerId'] ?? null;
	}
}