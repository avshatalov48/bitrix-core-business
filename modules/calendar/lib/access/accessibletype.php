<?php

namespace Bitrix\Calendar\Access;


use Bitrix\Main\Access\AccessibleItem;

interface AccessibleType
	extends AccessibleItem
{
	public function getXmlId(): string;
}