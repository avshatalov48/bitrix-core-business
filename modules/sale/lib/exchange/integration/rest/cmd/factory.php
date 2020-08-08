<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\Cmd;


class Factory
{
	static public function create($type)
	{
		if($type == Registry::CRM_DEAL_ADD_NAME)
		{
			return new CmdDealAdd();
		}
		elseif($type == Registry::CRM_CONTACT_ADD_NAME)
		{
			return new CmdContactAdd();
		}
		elseif($type == Registry::CRM_COMPANY_ADD_NAME)
		{
			return new CmdCompanyAdd();
		}
		elseif($type == Registry::CRM_ACTIVITY_ADD_NAME)
		{
			return new CmdActivityAdd();
		}
		else
		{
			return new Base();
		}
	}
}