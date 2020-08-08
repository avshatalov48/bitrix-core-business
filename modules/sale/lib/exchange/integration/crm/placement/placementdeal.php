<?php


namespace Bitrix\Sale\Exchange\Integration\CRM\Placement;


use Bitrix\Sale\Exchange\Integration\CRM\EntityType;

class PlacementDeal extends Base
{
	const REQUEST_PARAM_HANDLER = 'HANDLER';
	const REQUEST_PARAM_REST_APP_LAYOUT = 'REST_APP_LAYOUT';

	public function getType()
	{
		return Type::DEAL_DETAIL_TOOLBAR;
	}

	public function getEntityId()
	{
		return $this->fields->get('PLACEMENT_OPTIONS')['ID'];
	}

	public function getEntityTypeId()
	{
		return EntityType::DEAL;
	}

	public function getTypeHandler()
	{
		return $this->fields->get(self::REQUEST_PARAM_HANDLER);
	}

	public function getRestAppLayoutMode()
	{
		return $this->fields->get(self::REQUEST_PARAM_REST_APP_LAYOUT);
	}
}