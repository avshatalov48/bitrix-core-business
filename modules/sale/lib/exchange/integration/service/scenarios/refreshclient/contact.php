<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Scenarios\RefreshClient;

use \Bitrix\Sale\Exchange\Integration\Service\Batchable;

class Contact extends Client
{
	protected function getClient()
	{
		return new Batchable\Contact();
	}
}