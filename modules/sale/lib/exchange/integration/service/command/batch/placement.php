<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Command\Batch;


use Bitrix\Sale\Exchange\Integration;
use Bitrix\Sale\Exchange\Integration\Exception;

class Placement
	implements Integration\Service\Command\IProxy
{
	static public function getProxy()
	{
		return new Integration\Rest\RemoteProxies\CRM\Placement();
	}

	static public function binds(array $list)
	{
		$proxy = static::getProxy();
		$r = $proxy->binds($list);
		if($r->isSuccess())
		{
			$result = $r->getData()['DATA']['result'];
		}
		else
		{
			throw new Exception\CommandBatchException(implode(',', $r->getErrorMessages()));
		}

		return $result;
	}

	static public function unbinds(array $list)
	{
		$proxy = static::getProxy();
		$r = $proxy->unbinds($list);
		if($r->isSuccess())
		{
			$result = $r->getData()['DATA']['result'];
		}
		else
		{
			throw new Exception\CommandBatchException(implode(',', $r->getErrorMessages()));
		}

		return $result;
	}
}