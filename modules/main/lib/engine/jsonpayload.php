<?php

namespace Bitrix\Main\Engine;

use Bitrix\Main\Context;
use Bitrix\Main\HttpRequest;

/**
 * Class JsonPayload
 * @package Bitrix\Main\Engine
 */
final class JsonPayload
{
	/**
	 * Returns decoded data from JSON (proactive filter applied).
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->getDataList()->toArray();
	}

	/**
	 * Returns decoded data from JSON as a ParameterDictionary object, including raw data.
	 *
	 * @return \Bitrix\Main\Type\ParameterDictionary
	 */
	public function getDataList()
	{
		return Context::getCurrent()->getRequest()->getJsonList();
	}

	/**
	 * Get raw data from php://input.
	 *
	 * @return bool|string
	 */
	public function getRaw()
	{
		return HttpRequest::getInput();
	}
}
