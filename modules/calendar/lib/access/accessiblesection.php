<?php

namespace Bitrix\Calendar\Access;


use Bitrix\Main\Access\AccessibleItem;

interface AccessibleSection
	extends AccessibleItem
{
	public function getType(): string;
	public function getOwnerId(): int;
}