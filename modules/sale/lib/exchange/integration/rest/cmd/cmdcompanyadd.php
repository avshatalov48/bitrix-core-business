<?php
namespace Bitrix\Sale\Exchange\Integration\Rest\Cmd;


class CmdCompanyAdd extends CmdBase
{
	protected function getCmdName()
	{
		return Registry::getRegistry()[Registry::CRM_COMPANY_ADD_NAME];
	}
}