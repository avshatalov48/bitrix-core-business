<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Command\Line;


use Bitrix\Sale\Exchange\Integration;
use Bitrix\Sale\Exchange\Integration\Exception;

class App
	implements Integration\Service\Command\IProxy
{
	static public function getProxy()
	{
		return new Integration\Rest\RemoteProxies\CRM\App();
	}

	static public function optionSet(array $option)
	{
		$proxy = static::getProxy();
		$r = $proxy->optionSet($option);
		if($r->isSuccess())
		{
			$result = $r->getData()['DATA']['result'];
		}
		else
		{
			throw new Exception\CommandLineException(implode(',', $r->getErrorMessages()));
		}

		return $result;
	}
}