<?php

namespace Bitrix\Main\Service\MicroService\Filter;

use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\Event;

use Bitrix\Main\EventResult;
use Bitrix\Main\Service\MicroService\Client;

class Authorization extends \Bitrix\Main\Engine\ActionFilter\Base
{
	public function onBeforeAction(Event $event)
	{
		$request = Context::getCurrent()->getRequest()->toArray();
		$serverSignature = $request["BX_HASH"];
		unset($request["BX_HASH"]);
		$signature = Client::signRequest(
			$request,
			Client::getPortalType() === Client::TYPE_BITRIX24 ? Client::getLicenseCode() : ""
		);

		if(!$serverSignature || $serverSignature !== $signature)
		{
			$this->errorCollection[] = new Error("Request verification failed");
			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		return null;
	}
}