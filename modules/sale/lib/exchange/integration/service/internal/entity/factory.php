<?php


namespace Bitrix\Sale\Exchange\Integration\Service\Internal\Entity;


use Bitrix\Sale\Exchange\Integration\CRM\EntityType;

class Factory
{
	static public function create($type, array $values = null)
	{
		if($type == EntityType::ACTIVITY)
		{
			return new Activity($values);
		}
		elseif($type == EntityType::DEAL)
		{
			return new Deal($values);
		}
		elseif($type == EntityType::COMPANY)
		{
			return new Company($values);
		}
		elseif($type == EntityType::CONTACT)
		{
			return new Contact($values);
		}
		else
		{
			throw new \Bitrix\Main\NotSupportedException("BuilderEntity type: '".$type."' is not supported in current context");
		}
	}
}