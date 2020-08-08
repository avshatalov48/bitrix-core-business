<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Scenarios\RefreshClient;

use Bitrix\Sale\Exchange\Integration\Exception;
use \Bitrix\Sale\Exchange\Integration\Service\Batchable;

abstract class Client
{
	/**
	 * @return Batchable\Client
	 */
	abstract protected function getClient();

	public function refresh(array $params)
	{
		$list = $this->resolve($params);

		if(count($list)>0)
		{
			$void = $this
				->diff($list)
				->toArray();

			if(count($void)>0)
			{
				$this->adds($void);
			}
		}
		return $this;
	}

	public function resolve(array $params)
	{
		$client = $this->getClient();
		return $client::resolveFieldsValuesFromOrderList($params);
	}

	public function diff(array $params)
	{
		$client = $this->getClient();
		$client->init($params);

		$relationList = $client->relationListDstEntity();
		$res = count($relationList)>0 ? $client::proxyList(['ID'=>$relationList]):[];

		if(isset($res['error']) == false)
		{
			$list = [];
			foreach($res as $fields)
			{
				$list[] = $fields['ID'];
			}
			$client->relationDeleteByDstEntity($list);
		}
		else
		{
			throw new Exception\ScenariosException();
		}
		return $client->relationVoid();
	}

	public function adds(array $params)
	{
		$client = $this->getClient();

		$params = static::prepareFields($params);
		$client->init($params);
		$client->adds();

		return $client
			->getCollection();
	}

	static protected function prepareFields(array $params)
	{
		$result = [];

		foreach ($params as $index=>$param)
		{
			if(isset($param['EMAIL']))
			{
				$param['EMAIL'] = [
					0 => ['VALUE'=>$param['EMAIL'], 'VALUE_TYPE'=>'WORK']
				];
			}

			if(isset($param['PHONE']))
			{
				$param['PHONE'] = [
					0 => ['VALUE'=>$param['PHONE']]
				];
			}

			$result[$index] = $param;
		}

		return $result;
	}
}