<?php

namespace Bitrix\Im\V2\TariffLimit;

use Bitrix\Im\V2\Rest\PopupDataItem;

class TariffLimitPopupItem implements PopupDataItem
{
	private bool $isHistoryLimitExceeded;

	public function __construct(bool $isHistoryLimitExceeded)
	{
		$this->isHistoryLimitExceeded = $isHistoryLimitExceeded;
	}

	public function merge(PopupDataItem $item): PopupDataItem
	{
		return $this;
	}

	public static function getRestEntityName(): string
	{
		return 'tariffRestrictions';
	}

	public function toRestFormat(array $option = []): ?array
	{
		return [
			'isHistoryLimitExceeded' => $this->isHistoryLimitExceeded,
		];
	}
}