<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Scenarios\RefreshClient;

use \Bitrix\Sale\Exchange\Integration\Service\Batchable;

class Company extends Client
{
	protected function getClient()
	{
		return new Batchable\Company();
	}
}