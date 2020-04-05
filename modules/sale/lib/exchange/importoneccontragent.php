<?php

namespace Bitrix\Sale\Exchange;



final class ImportOneCContragent extends ImportOneCContragentBase
{
	static function getUserProfileEntityTypeId()
	{
		return EntityType::USER_PROFILE;
	}
}