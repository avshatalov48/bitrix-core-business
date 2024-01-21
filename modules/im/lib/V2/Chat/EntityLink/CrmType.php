<?php

namespace Bitrix\Im\V2\Chat\EntityLink;

use Bitrix\Im\V2\Chat\EntityLink;
use Bitrix\Main\Loader;

class CrmType extends EntityLink
{
	protected const HAS_URL = true;

	protected array $separatedEntityId;

	protected function getUrl(): string
	{
		if(!Loader::includeModule('crm'))
		{
			return '';
		}

		$crmType = $this->getCrmEntityType();
		$crmId = $this->getCrmEntityId();

		if ($crmType === '' || $crmId === 0)
		{
			return '';
		}

		return \Bitrix\Im\Integration\Crm\Common::getLink($crmType, $crmId);
	}

	protected function getCrmEntityType(): string
	{
		$this->separatedEntityId ??= explode('|', $this->id);

		return $this->separatedEntityId[0] ?? '';
	}

	protected function getCrmEntityId(): int
	{
		$this->separatedEntityId ??= explode('|', $this->id);

		return (int)($this->separatedEntityId[1] ?? 0);
	}
}