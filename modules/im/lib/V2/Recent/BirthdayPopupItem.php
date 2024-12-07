<?php

namespace Bitrix\Im\V2\Recent;

use Bitrix\Im\V2\Rest\PopupDataItem;

class BirthdayPopupItem implements PopupDataItem
{

	public function merge(PopupDataItem $item): PopupDataItem
	{
		return $this;
	}

	public static function getRestEntityName(): string
	{
		return 'birthdayList';
	}

	public function toRestFormat(array $option = []): array
	{
		return \Bitrix\Im\Integration\Intranet\User::getBirthdayForToday();
	}
}