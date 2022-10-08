<?php

namespace Bitrix\Location\Infrastructure\Service\DisputedAreaService;

use Bitrix\Main\IO\Path;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(Path::combine(__DIR__, 'scenario.php'));

class SevastopolDispute extends Dispute
{
	/**
	 * @inheritDoc
	 */
	protected function getInfo(): array
	{
		$country = in_array($this->currentRegion, ['ru', 'by'], true)
			? Loc::getMessage('LOCATION_ISTRUCTURE_DISPSRV_RUSSIA')
			: Loc::getMessage('LOCATION_ISTRUCTURE_DISPSRV_UKRAINE');

		return [
			'country' => $country,
		];
	}
}
