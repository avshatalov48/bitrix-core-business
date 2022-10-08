<?php

namespace Bitrix\Location\Infrastructure\Service\DisputedAreaService;

use Bitrix\Main\IO\Path;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
Loc::loadMessages(Path::combine(__DIR__, 'dispute.php'));

class CrimeaDispute extends Dispute
{
	/**
	 * @inheritDoc
	 */
	protected function getInfo(): array
	{
		if (in_array($this->currentRegion, ['ru', 'by'], true))
		{
			$country = Loc::getMessage('LOCATION_ISTRUCTURE_DISPSRV_RUSSIA');
			$state = Loc::getMessage('LOCATION_ISTRUCTURE_DISPSRV_REGION_CRIMEA_FOR_RUSSIA');
		}
		else
		{
			$country = Loc::getMessage('LOCATION_ISTRUCTURE_DISPSRV_UKRAINE');
			$state = Loc::getMessage('LOCATION_ISTRUCTURE_DISPSRV_REGION_CRIMEA_FOR_UKRAINE');
		}

		return [
			'country' => $country,
			'state' => $state,
		];
	}
}
