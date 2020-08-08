<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Batchable;


use Bitrix\Sale\Exchange\Integration;

abstract class Proxy extends Base
{
	/**
	 * @return Integration\Rest\RemoteProxies\CRM\Contact | Integration\Rest\RemoteProxies\CRM\Company | Integration\Rest\RemoteProxies\CRM\Activity | Integration\Rest\RemoteProxies\CRM\Deal
	 */
	abstract static protected function getProxy();

	static public function proxyList(array $filter)
	{
		$proxy = static::getProxy();
		$r = $proxy->getList([], $filter);
		if($r->isSuccess())
		{
			$result = $r->getData()['DATA']['result'];
		}
		else
		{
			$result['error'] = $r->getErrorMessages();
		}
		return $result;
	}
	static public function proxyAdds(array $list)
	{
		$proxy = static::getProxy();
		$list = static::prepareFieldsAdds($list);
		$r = $proxy->adds($list);
		if($r->isSuccess())
		{
			/*
			 * 		[result] => Array()
                    [result_error] => Array
                        (
                            [3] => Array
                                (
                                    [error] =>
                                    [error_description] => Не введено значение обязательного поля "Имя" или "Фамилия"
                                )
                        )
			 * */
			$result = $r->getData()['DATA']['result'];
		}
		else
		{
			$result['error'] = $r->getErrorMessages();
		}
		return $result;
	}

	static protected function prepareFieldsAdds($fields)
	{
		$result = [];
		foreach ($fields as $index=>$item)
		{
			$result[$index] = ['fields'=>$item];
		}
		return $result;
	}
}