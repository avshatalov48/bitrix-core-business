<?php

namespace Bitrix\Socialnetwork\Component\LogListCommon;

class Util
{
	public static function getRequest()
	{
		return \Bitrix\Main\Context::getCurrent()->getRequest();
	}

	public static function getCollapsedPinnedPanelItemsLimit(): int
	{
		return 3;
	}
}
