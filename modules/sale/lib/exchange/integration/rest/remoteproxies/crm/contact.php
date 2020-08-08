<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\CRM;


use Bitrix\Sale\Exchange\Integration\Rest;

class Contact extends Rest\RemoteProxies\Base
	implements IContact
{

	public function adds($list)
	{
		return $this
			->batch(Rest\Cmd\Registry::CRM_CONTACT_ADD_NAME, $list)
			->call();
	}

	public function getList($select=[], $filter, $order=[], $pageNavigation='')
	{
		return $this
			->cmd( Rest\Cmd\Registry::CRM_CONTACT_LIST_NAME, [
					'select' => $select,
					'filter' => $filter,
					'order' => $order,
					'pageNavigation' => $pageNavigation]
			)
			->call();
	}
}