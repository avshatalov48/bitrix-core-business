<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp;

use Bitrix\ImConnector\Library;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\MessageService\Providers\Edna\Constants\ChannelType;

class ConnectorLine
{
	protected \Bitrix\MessageService\Providers\Edna\EdnaRu $utils;

	public function __construct(\Bitrix\MessageService\Providers\Edna\EdnaRu $utils)
	{
		$this->utils = $utils;
	}

	public function getLineId(): ?int
	{
		if (!Loader::includeModule('imconnector'))
		{
			return null;
		}

		$statuses = \Bitrix\ImConnector\Status::getInstanceAllLine(Library::ID_EDNA_WHATSAPP_CONNECTOR);
		foreach ($statuses as $status)
		{
			if ($status->isConfigured())
			{
				return (int)$status->getLine();
			}
		}

		return null;
	}

	public function testConnection(): Result
	{
		return $this->utils->getChannelList(ChannelType::WHATSAPP);
	}

}