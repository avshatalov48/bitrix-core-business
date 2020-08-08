<?php


namespace Bitrix\Sale\Exchange\Integration\Connector\Placement;


use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Exchange\Integration;
use Bitrix\Sale\Exchange\Integration\CRM\Placement;

Loc::loadMessages(__FILE__);

class IntegrationB24Registry extends Base
{

	public function getTitle()
	{
		return Loc::getMessage('SALE_INTEGRATIONB24_REGISTRY_TITLE');
	}

	public function getGroupName()
	{
		return Loc::getMessage('SALE_INTEGRATIONB24_REGISTRY_GROUPNAME');
	}

	public function getPlacement()
	{
		return Placement\Type::DEAL_DETAIL_TOOLBAR_NAME;
	}

	public function getPlacmentHandler()
	{
		return $this->getAppUrl().'?'.http_build_query([
			Placement\PlacementDeal::REQUEST_PARAM_HANDLER => Integration\HandlerType::ORDER_REGISTRY,
			Placement\PlacementDeal::REQUEST_PARAM_REST_APP_LAYOUT => 'Y'
		]);
	}
}