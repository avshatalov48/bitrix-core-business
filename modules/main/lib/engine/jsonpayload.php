<?php
namespace Bitrix\Main\Engine;

use Bitrix\Main\HttpRequest;
use Bitrix\Main\Web\Json;

/**
 * Class JsonPayload
 * @package Bitrix\Main\Engine
 */
final class JsonPayload
{
	/**
	 * Get data.
	 *
	 * @return array|mixed
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getData()
	{
		return Json::decode($this->getRaw());
	}

	/**
	 * Get raw data.
	 *
	 * @return bool|string
	 */
	public function getRaw()
	{
		return HttpRequest::getInput();
	}
}