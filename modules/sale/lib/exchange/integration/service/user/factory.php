<?php
namespace Bitrix\Sale\Exchange\Integration\Service\User;


use Bitrix\Sale\Exchange\Integration\Service\User\Entity;

class Factory
{
	static public function create($typeId)
	{
		if($typeId == EntityType::TYPE_I)
		{
			return new Entity\Contact();
		}
		elseif($typeId == EntityType::TYPE_E)
		{
			return new Entity\Company();
		}
		else
		{
			throw new \Bitrix\Main\NotSupportedException("Client type: '".$typeId."' is not supported in current context");
		}
	}
}