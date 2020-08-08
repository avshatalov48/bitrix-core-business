<?php


namespace Bitrix\Sale\Exchange\Integration\CRM\Placement;


class Factory
{
	static public function create($type, $fields)
	{
		if($type == Type::DEAL_DETAIL_TOOLBAR)
		{
			return new PlacementDeal($fields);
		}
		elseif ($type == Type::DEFAULT_TOOLBAR)
		{
			return new PlacementDefault($fields);
		}
		else
		{
			throw new \Bitrix\Main\NotSupportedException("AppPlacementType: '".$type."' is not supported in current context");
		}
	}
}