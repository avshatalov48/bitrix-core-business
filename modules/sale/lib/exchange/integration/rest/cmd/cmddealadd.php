<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\Cmd;


class CmdDealAdd extends CmdBase
{
	protected function getCmdName()
	{
		return Registry::getRegistry()[Registry::CRM_DEAL_ADD_NAME];
	}
}