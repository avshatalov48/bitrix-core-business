<?php


namespace Bitrix\Sale\Exchange;


class ImportOneCContragentCRM extends ImportOneCContragentBase
{
	static function getUserProfileEntityTypeId()
	{
		return EntityType::USER_PROFILE_CONTACT_COMPANY;
	}
}