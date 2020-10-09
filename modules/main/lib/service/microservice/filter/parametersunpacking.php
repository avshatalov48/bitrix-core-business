<?php

namespace Bitrix\Main\Service\MicroService\Filter;

use Bitrix\Main\Context;
use Bitrix\Main\Event;
use Bitrix\Main\Web\Json;

class ParametersUnpacking extends \Bitrix\Main\Engine\ActionFilter\Base
{
	public function onBeforeAction(Event $event)
	{
		$request = Context::getCurrent()->getRequest();
		$packedParameters = $request->get("serializedParameters");
		if(is_string($packedParameters))
		{
			$decodedParameters = gzdecode(base64_decode($packedParameters));

			if(is_string($decodedParameters))
			{
				$unpackedParameters = Json::decode($decodedParameters);
				if(is_array($unpackedParameters))
				{
					/** @var \Bitrix\Main\Engine\ActionFilter\Base $this */
					$this->getAction()->getController()->setSourceParametersList([
						$unpackedParameters
					]);
				}
			}
		}
	}
}