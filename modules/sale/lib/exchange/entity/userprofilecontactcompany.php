<?php


namespace Bitrix\Sale\Exchange\Entity;


use Bitrix\Crm\Order\Matcher\EntityMatchManager;
use Bitrix\Crm\Order\Order;
use Bitrix\Sale\Exchange\EntityType;
use Bitrix\Sale\Result;

class UserProfileContactCompany extends UserProfileImport
{
	public function getOwnerTypeId()
	{
		return EntityType::USER_PROFILE_CONTACT_COMPANY;
	}

	public function add(array $params)
	{
		$result = new Result();

		$r = parent::add($params);
		if($r->isSuccess())
		{
			$userId = $this->getEntity()->getField('ID');
			$property = $params["ORDER_PROP"];

			$order = Order::create($userId);
			$order->getPropertyCollection()->setValuesFromPost(['PROPERTIES'=>$property], []);

			EntityMatchManager::getInstance()->match($order);
		}
		return $result;
	}
}