<?php

namespace Bitrix\Location\Infrastructure;

class EventHandler
{
	public static function onUIFormInitialize(): void
	{
		\Bitrix\Main\UI\Extension::load('location.widget');
	}
}