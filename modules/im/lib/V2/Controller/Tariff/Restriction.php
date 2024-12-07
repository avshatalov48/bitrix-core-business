<?php

namespace Bitrix\Im\V2\Controller\Tariff;

use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\TariffLimit\Limit;

class Restriction extends BaseController
{
	/**
	 * @restMethod im.v2.Tariff.Restriction.get
	 */
	public function getAction(): ?array
	{
		return Limit::getInstance()->getRestrictions();
	}
}