<?php


namespace Bitrix\Sale\Exchange\Integration\CRM\Placement;


use Bitrix\Sale\Exchange\Integration\CRM\EntityType;

class PlacementDefault extends Base
{
	const VIEW_ACTION = 'view_activity';

	public function getType()
	{
		return Type::DEFAULT_TOOLBAR;
	}

	public function getEntityId()
	{
		return $this->fields->get('PLACEMENT_OPTIONS')['activity_id'];
	}

	public function getEntityTypeId()
	{
		$type = $this->getAction();

		if($type == static::VIEW_ACTION)
		{
			return EntityType::ACTIVITY;
		}
		else
		{
			throw new \Bitrix\Main\NotSupportedException("Action AppPlacementType: '".$type."' is not supported in current context");
		}
	}

	protected function getAction()
	{
		return $this->fields->get('PLACEMENT_OPTIONS')['action'];
	}
}