<?php

namespace Bitrix\Mail\Blacklist;

class ItemType
{

	const DOMAIN  = 1;
	const EMAIL   = 2;
	//const PATTERN = 3;

	public static function resolveByValue($value)
	{
		if (strpos($value, '@') > 0)
			return ItemType::EMAIL;
		
		return ItemType::DOMAIN;
	}

}
