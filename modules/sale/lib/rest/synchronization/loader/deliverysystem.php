<?php


namespace Bitrix\Sale\Rest\Synchronization\Loader;


use Bitrix\Main\ArgumentException;

class DeliverySystem extends Entity
{
	public function getFieldsByExternalId($xmlId)
	{
		if($xmlId === "")
		{
			return null;
		}

		foreach(\Bitrix\Sale\Delivery\Services\Manager::getActiveList() as $row)
		{
			if($xmlId == $row['XML_ID'])
				return $row['ID'];
		}

		return null;
	}

}