<?php

namespace Bitrix\Rest\FormConfig;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Web\Json;

class ConfigStoreRequest implements ConfigStoreInterface
{
	private string $parameterName;

	public function __construct(string $parameterName)
	{
		$this->parameterName = $parameterName;
	}

	public function provide(): array
	{
		$request = Application::getInstance()->getContext()->getRequest();
		$jsonConfig = $request->getPost('config');
		try
		{
			$config = Json::decode($jsonConfig);
		}
		catch (ArgumentException $exception)
		{
			return [];
		}

		return $config;
	}
}