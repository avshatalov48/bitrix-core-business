<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\CRM;


use Bitrix\Sale\Exchange\Integration\Rest;

class Deal extends Rest\RemoteProxies\Base
	implements IDeal
{
	public function adds($list)
	{
		return $this
			->batch(Rest\Cmd\Registry::CRM_DEAL_ADD_NAME, $list)
			->call();
	}

	public function contactItemsGet($id)
	{
		return $this
			->cmd( Rest\Cmd\Registry::CRM_DEAL_CONTACT_ITEMS_GET_NAME, [
				'id' => $id])
			->call();
	}

	public function contactItemsSet($id, $items)
	{
		return $this
			->cmd( Rest\Cmd\Registry::CRM_DEAL_CONTACT_ITEMS_SET_NAME, [
				'id' => $id,
				'items' => $items])
			->call();
	}
}