<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\Cmd;


class CmdContactAdd extends CmdBase
{
	protected function getCmdName()
	{
		return Registry::getRegistry()[Registry::CRM_CONTACT_ADD_NAME];
	}
}