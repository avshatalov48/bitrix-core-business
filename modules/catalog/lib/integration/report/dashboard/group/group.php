<?php

namespace Bitrix\Catalog\Integration\Report\Dashboard\Group;

interface Group
{
	public function getGroupKey(): string;
	public function getGroupTitle(): string;
}
